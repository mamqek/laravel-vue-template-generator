<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LocaleController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use App\Http\Controllers\DynamicPageController;
use App\Http\Controllers\DirectusWebhookController;

/*
|--------------------------------------------------------------------------
| API (session-auth) under /api/v1
|--------------------------------------------------------------------------
| These routes live in web.php to leverage the "web" middleware stack
| (sessions + CSRF). Do NOT add the custom 'session' group here — it's
| already active. Leave CSRF enabled for state-changing requests.
*/
Route::prefix('api/v1')->name('api.v1.')->group(function () {
    // --- I18N ---
    Route::prefix('translations')
        ->controller(LocaleController::class)
        ->group(function () {
            Route::get('/',        'getLocales')->name('translations.index');
            Route::get('{locale}', 'getMessages')->name('translations.messages');
            Route::post('{locale}', 'changeLocale')
                ->name('translations.change');
        });

    // --- AUTH (Google nested inside auth group) ---
    Route::prefix('auth')
        ->controller(AuthController::class)
        ->group(function () {
            Route::post('login',        'login')->name('auth.login');
            Route::post('register',     'register')->name('auth.register');
            Route::post('authenticate', 'authenticate')->name('auth.authenticate');
            Route::post('logout',       'logout')->middleware('auth.session')->name('auth.logout');
            Route::post('check-email',  'checkEmail')->name('auth.checkEmail');

            // Social auth (Google) inside auth group
            Route::prefix('google')->group(function () {
                Route::get('redirect', 'redirectToGoogle')->name('auth.google.redirect');
                Route::get('callback', 'handleGoogleCallback')->name('auth.google.callback');
            });
        });

    Route::post('directus/webhooks/cache', [DirectusWebhookController::class, 'handle'])
        ->name('directus.webhook')
        ->withoutMiddleware([ValidateCsrfToken::class]);

    // --- PAGES ---
    Route::prefix('page')
        ->controller(DynamicPageController::class)
        ->group(function () {
            Route::get('page/{table}', 'apiByTable')->name('page.byTable');
            Route::get('/',            'apiByPath')->name('page.byPath');  
        });
});

/*
|--------------------------------------------------------------------------
| SPA catch-all (must be last)
|--------------------------------------------------------------------------
*/
// Route::get('/{vue_capture?}', function () {
//     return view('welcome');
// })->where('vue_capture', '[\/\w\.-]*');

Route::get('/{vue_capture?}', [DynamicPageController::class, 'show'])
    ->where('vue_capture',
    '^(?!\.well-known)(?!robots\.txt$)(?!favicon\.ico$)[\/\w\.-]*$'
);