<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class LocaleController extends Controller
{

    function getMessages($locale) {
        $path = base_path("lang/{$locale}.json");

        if (!File::exists($path)) {
            abort(404, __('response.*_not_found', ['attribute' => __('language')]));
        }
    
        return response()->file($path);
    }

    function getLocales() {
        $langDirectory = base_path("lang");
        $subDirectories = File::directories($langDirectory);
        $locales = array_map(function($dir) {
            return basename($dir);
        }, $subDirectories);

        return $locales;
    }

    function changeLocale($locale) {
        try {
            App::setLocale($locale);
            session(['locale' => $locale]);

            return response()->json([
                'status' => __('response.success'),
                // 'message' => 'Succesfully changed locale',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status' => __('response.error'),
                'message' => __('response.error_while_*', ['action' => __('response.changing'), 'attribute' => __('language')]),
                'error' => $e->getMessage()
            ], 500);
        }

    }
}
