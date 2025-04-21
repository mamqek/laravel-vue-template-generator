<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <link rel='shortcut icon' type="image/png" href="{{ asset('images/logo.png') }}">

        <!-- TODO: Satisfactory calculator graphical with algular -->

        @vite([
            'resources/css/app.css',
            'resources/css/reset.css',
            'resources/css/elements.css',
            'resources/js/app.js'
        ])

        <title>Mamqek's Dev space</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Icons -->
        <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>
