<?php 

return [

    'paths' => ['api/*', 'satisfactory/*'], // specify your endpoints

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:4200'], // your frontend URL

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
