<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('translations/{locale}', ['App\Http\Controllers\LocaleController', 'getMessages']);
Route::get('translations', ['App\Http\Controllers\LocaleController', 'getLocales']);
Route::post('translations/{locale}', ['App\Http\Controllers\LocaleController', 'changeLocale']);

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('authenticate', [AuthController::class, 'authenticate']);
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::get('/{vue_capture?}', function () {
    return view('welcome');
})->where('vue_capture', '^(?!\.well-known)(?!robots\.txt$)(?!favicon\.ico$)[\/\w\.-]*$');
