<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\DirectusService;

class DynamicPageController extends Controller
{
    public function __construct(private readonly DirectusService $directus)
    {
    }

    /**
     * Render the SPA shell and inject initial page data based on current path.
     */
    public function show(Request $request, ?string $vue_capture = null)
    {
        $path = '/' . ltrim($vue_capture ?? '/', '/');
        Log::info("{$vue_capture} Incoming request for path: '{$path}'");
        [$table, $slug] = $this->resolveTableFromPath($path);

        Log::info("{$vue_capture} Resolved path '{$path}' to table '{$table}' and slug '{$slug}'");

        $page = $this->getPageCached($table);
        
        if (!$page) {
            Log::warning("{$vue_capture} No cached page found for table '{$table}'");
        }
        Log::info("{$vue_capture} Serving page for table '{$table}': " . json_encode($page));

        return view('welcome', [
            'initialPage'  => $page,
            'initialTable' => $table,
        ]);
    }

    /**
     * API: fetch page by table (optional, nice for debugging or prefetching).
     * GET /api/v1/pages/{table}
     */
    public function apiByTable(string $table)
    {
        $table = preg_replace('/[^a-z0-9_]/i', '_', $table);
        $page = $this->getPageCached($table);

        if (!$page) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['table' => $table, 'data' => $page]);
    }

    /**
     * API: fetch page by path using the SAME logic as show().
     * GET /api/v1/page?path=/about/team
     */
    public function apiByPath(Request $request)
    {
        $path = $request->query('path', '/');
        [$table] = $this->resolveTableFromPath($path);

        $page = $this->getPageCached($table);
        if (!$page) {
            return response()->json(['message' => 'Not found'], 404);
        }
        return response()->json(['table' => $table, 'data' => $page]);
    }

    // ---------------------------
    // Internals (kept in-class)
    // ---------------------------

    /**
     * Central mapping logic: URL path -> (table, slug).
     * Everything is local to this controller as requested.
     */
    private function resolveTableFromPath(string $path): array
    {
        $path = '/' . ltrim($path, '/');

        // Exceptions (inline; no config file)
        $exceptions = [
            '/'    => 'home',
            'home' => 'home',
        ];

        // Strategy: 'first-segment' | 'full-dashed' | 'full-underscored'
        $pathStrategy = 'first-segment';


        // 1) Check exceptions (raw match)
        if (array_key_exists($path, $exceptions)) {
            $slug = $exceptions[$path];
        } else {
            $clean = trim($path, '/');
            if ($clean === '') {
                $slug = $exceptions['/'] ?? 'home';
            } else {
                switch ($pathStrategy) {
                    case 'full-dashed':
                        $slug = $clean; // keep dashes
                        break;
                    case 'full-underscored':
                        $slug = str_replace('/', '__', $clean);
                        break;
                    case 'first-segment':
                    default:
                        $slug = explode('/', $clean)[0]; // /about/team -> 'about'
                        break;
                }
            }
        }

        // Slug -> table
        $table = $slug ? str_replace('-', '_', $slug) : null;

        return [$table, $slug];
    }

    private function getPageCached(?string $table): ?array
    {
        if (!$table) {
            return null;
        }

        try {
            return $this->directus->singleton($table, [
                'fields' => [
                    'translations.*.*.*',
                    'translations.*.*.image.*',
                    'translations.*.*.side_image.*',
                    'translations.sections.*.header_image.*',
                    'translations.sections.*.products.*.*',
                    'translations.sections.*.products.*.image.*',
                    'translations.sections.*.products.*.translations.*',
                ],
                'backlink' => 'false',
            ]);
        } catch (\Throwable $e) {
            Log::error("Error fetching page from Directus for table '{$table}': " . $e->getMessage());

            return null;
        }
    }
}
