<?php

namespace App\Services;

use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Centralized client responsible for fetching content from Directus and
 * maintaining the cache index that lets us invalidate data collection-wide.
 *
 * Controllers only interact with this service so that authentication, cache
 * keys, and webhook refresh behaviour remain consistent across the codebase.
 */
class DirectusService
{
    protected string $baseUrl;

    protected ?string $token;

    protected bool $cacheEnabled;

    protected ?int $defaultTtl;

    /**
     * Build a Directus service using the configured cache repository.
     */
    public function __construct(protected CacheRepository $cache)
    {
        $this->baseUrl = rtrim((string) config('services.directus.url'), '/');
        $this->token = config('services.directus.token');

        $enabled = data_get(config('services.directus'), 'cache.enabled', true);
        $normalizedEnabled = is_bool($enabled)
            ? $enabled
            : filter_var($enabled, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $this->cacheEnabled = $normalizedEnabled ?? (bool) $enabled;

        $ttl = data_get(config('services.directus'), 'cache.ttl');
        $this->defaultTtl = is_numeric($ttl) ? (int) $ttl : null;
    }

    /**
     * Fetch a collection from Directus, caching the response using the
     * normalized collection name and query arguments.
     */
    public function items(string $collection, array $query = [], array $options = []): array
    {
        return $this->request('get', "/items/{$collection}", $query, $options, $collection);
    }

    /**
     * Fetch a single record from a collection and return the Directus `data`
     * payload, caching the response using the record identifier.
     */
    public function item(string $collection, int|string $id, array $query = [], array $options = []): ?array
    {
        return $this->request(
            'get',
            "/items/{$collection}/" . rawurlencode((string) $id),
            $query,
            $options + ['unwrap' => 'data'],
            $collection
        );
    }

    /**
     * Retrieve a Directus singleton and unwrap the response into the cached
     * body so callers can treat it like a simple associative array.
     */
    public function singleton(string $collection, array $query = [], array $options = []): ?array
    {
        return $this->request('get', "/items/{$collection}", $query, $options + ['unwrap' => 'data'], $collection);
    }

    /**
     * Forget every cache entry currently tracked for the given collection.
     *
     * This is used by webhooks and mutation paths to evict stale payloads
     * before the next request rebuilds the cache.
     */
    public function flushCollection(string $collection): string
    {
        $collection = $this->normalizeCollectionName($collection);
        $indexKey = $this->collectionIndexKey($collection);
        $entries = $this->normalizeIndexEntries($this->cache->get($indexKey, []));

        foreach (array_keys($entries) as $key) {
            $this->cache->forget($key);
        }

        $this->cache->forget($indexKey);

        return $collection;
    }

    /**
     * Flush the collection cache and immediately replay the stored request
     * metadata so the cache is warm again by the time the webhook returns.
     */
    public function refreshCollection(string $collection): array
    {
        $collection = $this->normalizeCollectionName($collection);
        $indexKey = $this->collectionIndexKey($collection);
        $entries = $this->normalizeIndexEntries($this->cache->get($indexKey, []));

        if (empty($entries)) {
            $this->flushCollection($collection);

            return [
                'collection' => $collection,
                'warmed' => [],
                'skipped' => [],
            ];
        }

        $metadataList = $entries;
        $this->flushCollection($collection);

        $warmed = [];
        $skipped = [];

        foreach ($metadataList as $cacheKey => $metadata) {
            if (!is_array($metadata)) {
                $skipped[] = $cacheKey;
                continue;
            }

            $method = strtolower((string) ($metadata['method'] ?? 'get'));
            $path = (string) ($metadata['path'] ?? '');
            $query = is_array($metadata['query'] ?? null) ? $metadata['query'] : [];
            $ttl = array_key_exists('ttl', $metadata) ? $metadata['ttl'] : null;

            if ($path === '') {
                $skipped[] = $cacheKey;
                continue;
            }
            Log::info('Directus refresh', [
                'collection' => $collection,
                'cache_key' => $cacheKey,
                'method' => $method,
                'path' => $path,
                'query' => $query,
                'ttl' => $ttl,
            ]);
            try {
                $response = $this->performRequest($method, $path, $query);
            } catch (Throwable $exception) {
                Log::warning('Directus refresh failed', [
                    'collection' => $collection,
                    'cache_key' => $cacheKey,
                    'error' => $exception->getMessage(),
                ]);

                $skipped[] = $cacheKey;
                continue;
            }

            if ($ttl === null) {
                $this->cache->forever($cacheKey, $response);
            } else {
                $ttl = (int) $ttl;

                if ($ttl <= 0) {
                    $skipped[] = $cacheKey;
                    continue;
                }

                $this->cache->put($cacheKey, $response, $ttl);
            }

            $this->trackCacheKey($collection, $cacheKey, [
                'method' => $method,
                'path' => $path,
                'query' => $query,
                'unwrap' => $metadata['unwrap'] ?? null,
                'ttl' => $ttl === null ? null : (int) $ttl,
            ]);

            $warmed[] = $cacheKey;
        }

        return [
            'collection' => $collection,
            'warmed' => $warmed,
            'skipped' => $skipped,
        ];
    }

    /**
     * Core request helper that applies caching, computes the cache key, and
     * optionally unwraps nested response data for callers.
     */
    protected function request(string $method, string $path, array $query, array $options, ?string $collection): mixed
    {
        $normalizedQuery = $this->normalizeQuery($query);
        $unwrap = $options['unwrap'] ?? null;
        $cacheEnabled = $options['cache'] ?? $this->cacheEnabled;
        $collectionKey = $collection ? $this->normalizeCollectionName($collection) : trim($path, '/');

        $callback = function () use ($method, $path, $normalizedQuery) {
            return $this->performRequest($method, $path, $normalizedQuery);
        };

        if ($cacheEnabled) {
            $ttl = $this->resolveTtl($options);
            if ($ttl === 0) {
                $response = $callback();
            } else {
                $cacheKey = $this->cacheKey($collectionKey, $path, $normalizedQuery, $unwrap);
                $metadata = [
                    'method' => $method,
                    'path' => $path,
                    'query' => $normalizedQuery,
                    'unwrap' => $unwrap,
                    'ttl' => $ttl,
                ];

                if ($this->cache->has($cacheKey)) {
                    Log::info('Directus cache hit', ['key' => $cacheKey]);
                }

                $response = $ttl === null
                    ? $this->cache->rememberForever($cacheKey, $callback)
                    : $this->cache->remember($cacheKey, $ttl, $callback);

                Log::info('Directus cache', [
                    'collection' => $collectionKey,
                    'cache_key' => $cacheKey,
                    'method' => $method,
                    'path' => $path,
                    'query' => $normalizedQuery,
                    'unwrap' => $unwrap,
                    'ttl' => $ttl,
                ]);

                $this->trackCacheKey($collectionKey, $cacheKey, $metadata);
            }
        } else {
            $response = $callback();
        }

        if ($unwrap) {
            return Arr::get($response, $unwrap);
        }

        return $response;
    }

    /**
     * Execute the HTTP call against Directus and return the decoded JSON
     * response. The retry behaviour is configured in {@see http()}.
     */
    protected function performRequest(string $method, string $path, array $query): array
    {
        $client = $this->http();
        $method = strtolower($method);

        if (!method_exists($client, $method)) {
            throw new \BadMethodCallException("Unsupported HTTP method '{$method}'");
        }

        return $client->{$method}($path, $query)->throw()->json();
    }

    /**
     * Prepare the base HTTP client with authentication, retry, and JSON
     * headers so individual calls only need to specify method and payload.
     */
    protected function http(): PendingRequest
    {
        $request = Http::baseUrl($this->baseUrl)->acceptJson()->retry(2, 250);

        if ($this->token) {
            $request = $request->withToken($this->token);
        }

        return $request;
    }

    /**
     * Deduplicate fields, drop null filters, and disable backlinks so that the
     * resulting query string is stable and leads to deterministic cache keys.
     */
    protected function normalizeQuery(array $query): array
    {
        if (array_key_exists('fields', $query) && is_array($query['fields'])) {
            $query['fields'] = array_values(array_unique($query['fields']));
        }

        $query = array_filter($query, static fn ($value) => $value !== null);

        if (!array_key_exists('backlink', $query)) {
            $query['backlink'] = 'false';
        }

        return $query;
    }

    /**
     * Build the cache key using the collection namespace and a hash of the
     * request path, query, and unwrap target so distinct queries never collide.
     */
    protected function cacheKey(string $collection, string $path, array $query, ?string $unwrap): string
    {
        return implode(':', [
            'directus',
            $collection,
            sha1($path . '|' . serialize($query) . '|' . ($unwrap ?? '')),
        ]);
    }

    /**
     * The cache key that stores metadata for every cached payload per
     * collection, used when we flush or refresh a collection.
     */
    protected function collectionIndexKey(string $collection): string
    {
        return "directus:index:{$collection}";
    }

    /**
     * Register a cache entry and its metadata in the per-collection index.
     */
    protected function trackCacheKey(string $collection, string $cacheKey, array $metadata): void
    {
        $this->mutateCollectionIndex($collection, function (array $entries) use ($cacheKey, $metadata) {
            $entries[$cacheKey] = $metadata;

            return $entries;
        });
    }

    /**
     * Execute an atomic read-modify-write cycle on the collection index,
     * preferring cache store locks when they are available.
     */
    protected function mutateCollectionIndex(string $collection, callable $callback): void
    {
        $collection = $this->normalizeCollectionName($collection);
        $indexKey = $this->collectionIndexKey($collection);

        $runner = function () use ($indexKey, $callback) {
            $entries = $this->normalizeIndexEntries($this->cache->get($indexKey, []));
            $updated = $callback($entries);

            if (!is_array($updated)) {
                $updated = $entries;
            }

            $this->cache->forever($indexKey, $updated);
        };

        if ($lockProvider = $this->lockProvider()) {
            $lockName = "{$indexKey}:lock";
            $lockSeconds = (int) data_get(config('services.directus'), 'cache.index_lock_seconds', 10);
            $lockWait = (int) data_get(config('services.directus'), 'cache.index_lock_wait', $lockSeconds);

            try {
                $lockProvider->lock($lockName, $lockSeconds)->block($lockWait, $runner);

                return;
            } catch (LockTimeoutException $exception) {
                Log::warning('Directus cache index lock timeout', [
                    'collection' => $collection,
                    'lock' => $lockName,
                    'error' => $exception->getMessage(),
                ]);
            } catch (Throwable $exception) {
                Log::warning('Directus cache index lock error', [
                    'collection' => $collection,
                    'lock' => $lockName,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        $runner();
    }

    /**
     * Resolve the underlying lock-capable cache store so we can guard index
     * mutations against concurrent writers.
     */
    protected function lockProvider(): ?LockProvider
    {
        if ($this->cache instanceof LockProvider) {
            return $this->cache;
        }

        if (method_exists($this->cache, 'getStore')) {
            $store = $this->cache->getStore();

            if ($store instanceof LockProvider) {
                return $store;
            }
        }

        return null;
    }

    /**
     * Determine the TTL that should be applied to the cached response.
     *
     * Returns null for "forever" caches, zero to disable caching entirely, or
     * a positive integer for the number of seconds.
     */
    protected function resolveTtl(array $options): int|null
    {
        if (array_key_exists('ttl', $options)) {
            $ttl = $options['ttl'];
        } else {
            $ttl = $this->defaultTtl;
        }

        if ($ttl === null) {
            return null;
        }

        $ttl = (int) $ttl;

        return $ttl < 0 ? 0 : $ttl;
    }

    /**
     * Normalize collection names so different casings map to the same index.
     */
    protected function normalizeCollectionName(string $collection): string
    {
        return Str::slug($collection, '_');
    }

    /**
     * Ensure the stored index entries are associative arrays keyed by cache
     * key. Old formats that stored numeric arrays are normalized on read.
     */
    protected function normalizeIndexEntries(mixed $entries): array
    {
        if (!is_array($entries) || $entries === []) {
            return [];
        }

        if (!Arr::isAssoc($entries)) {
            $normalized = [];

            foreach ($entries as $value) {
                if (is_string($value)) {
                    $normalized[$value] = null;
                }
            }

            return $normalized;
        }

        return $entries;
    }
}
