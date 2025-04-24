<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    */

    'paths' => ['api/*', 'oauth/*', 'sanctum/csrf-cookie', 'login', 'logout'],
    
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:4000')],
    
    'allowed_origins_patterns' => [],
    
    'allowed_methods' => ['*'],
    
    'allowed_headers' => [
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'Content-Type',
        'X-Requested-With',
        'Authorization',
        'Origin',
        'Accept',
    ],
    
    'exposed_headers' => ['Authorization'],
    
    'max_age' => 86400, // 24 hours
    
    'supports_credentials' => true, // Important: must be true for Sanctum to work with SPA
];