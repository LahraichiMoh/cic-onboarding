<?php

return [
    'twig' => [
        'cache' => 'dev' === env('APP_ENV') ? false : base_path('storage/cache'),
        'debug' => 'dev' === env('APP_ENV'),
        'globals' => [
            'app_name' => env('APP_NAME'),
        ],
        'extensions' => [
        ],
    ],
];
