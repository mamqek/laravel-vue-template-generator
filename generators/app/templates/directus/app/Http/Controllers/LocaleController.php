<?php

namespace App\Http\Controllers;

use App\Models\Language;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;

class LocaleController extends Controller
{
    function getLocales() {
        return response()->json([
            'languages' => Language::all(),
            'current' => App::getLocale(),
        ]);
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
