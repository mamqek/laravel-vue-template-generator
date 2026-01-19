<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Preconnects & DNS prefetches for performance -->
        <link rel="preconnect" href="{{config('services.directus.url')}}" crossorigin>
        <link rel="dns-prefetch" href="{{config('services.directus.url')}}">


        <!-- Title -->
        <title>LAravel Vue Directus Template</title>

        @vite([
            'resources/css/main.css',
            'resources/js/app.js'
        ])

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">        
                
        <!-- Icons -->
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
        <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

        <script id="initial-page" type="application/json">
            {!! json_encode([
            'page'  => $initialPage,
            'table' => $initialTable,
            ], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) !!}
        </script>
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
