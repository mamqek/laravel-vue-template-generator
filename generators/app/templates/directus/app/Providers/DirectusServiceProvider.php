<?php

namespace App\Providers;

use App\Services\DirectusService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

/**
 * Registers the Directus service as a singleton so every part of the app uses
 * the same cache store and configuration when talking to Directus.
 */
class DirectusServiceProvider extends ServiceProvider
{
    /**
     * Bind the Directus service using the configured cache store, allowing
     * deployments to override it without code changes.
     */
    public function register(): void
    {
        $this->app->singleton(DirectusService::class, function () {
            $store = config('services.directus.cache.store');
            $targetStore = $store ?: config('cache.default');

            return new DirectusService(Cache::store($targetStore));
        });
    }
}
