<?php

namespace App\Http\Controllers;

use App\Services\DirectusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Receives Directus webhook calls and asks the Directus service to refresh the
 * relevant collection caches. The controller is intentionally small so the
 * cache logic lives entirely inside the service.
 */
class DirectusWebhookController extends Controller
{
    public function __construct(private readonly DirectusService $directus)
    {
    }

    /**
     * Validate the shared secret (when configured), resolve the collections in
     * the payload, and trigger cache refreshes for each of them.
     */
    public function handle(Request $request): JsonResponse
    {
        $secret = config('services.directus.webhook_secret');
        $provided = $request->header('X-Directus-Secret');

        if ($secret && (!is_string($provided) || !hash_equals($secret, $provided))) {
            Log::warning('Directus webhook rejected due to invalid secret.');

            abort(401, 'Invalid webhook secret.');
        }

        $collections = $this->extractCollections($request);

        if (empty($collections)) {
            return response()->json([
                'ok' => false,
                'reason' => 'missing collection name',
            ], 400);
        }

        $results = [];

        foreach ($collections as $collection) {
            $refreshed = $this->directus->refreshCollection($collection);

            $results[] = [
                'requested' => $collection,
                'collection' => $refreshed['collection'] ?? $collection,
                'warmed' => $refreshed['warmed'] ?? [],
                'skipped' => $refreshed['skipped'] ?? [],
            ];
        }

        Log::info('Directus cache refresh triggered', ['collections' => $results]);

        return response()->json([
            'ok' => true,
            'collections' => $results,
        ]);
    }

    /**
     * Pull every collection reference we understand out of the webhook payload.
     * Directus emits different shapes depending on the trigger type, so we
     * gather both singular and plural keys and normalise the result.
     */
    private function extractCollections(Request $request): array
    {
        $names = [];
        $candidates = array_filter([
            $request->input('collection'),
            $request->input('table'),
        ]);

        if ($request->has('collections') && is_array($request->input('collections'))) {
            $candidates = array_merge($candidates, $request->input('collections'));
        }

        $payload = $request->input('payload');
        if (is_array($payload)) {
            if (isset($payload['collection'])) {
                $candidates[] = $payload['collection'];
            }

            if (isset($payload['collections']) && is_array($payload['collections'])) {
                $candidates = array_merge($candidates, $payload['collections']);
            }
        }

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '') {
                $names[] = $candidate;
            }
        }

        return array_values(array_unique($names));
    }
}
