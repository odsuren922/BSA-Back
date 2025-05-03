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
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => true, // Important: must be true for Sanctum to work with SPA
];