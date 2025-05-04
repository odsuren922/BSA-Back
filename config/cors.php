<?php

return [
    'paths' => [
        'api/*', 
        'oauth/*', 
        'sanctum/csrf-cookie', 
        'login', 
        'logout', 
        'auth',
        'api/user',
        'api/oauth/*',
    ],
    
    'allowed_origins' => [
        'http://localhost:4000', 
        'http://127.0.0.1:4000', 
        env('FRONTEND_URL', 'http://localhost:4000')
    ],

    'allowed_origins_patterns' => [],
    
    'allowed_methods' => ['*'],
    
    'allowed_headers' => [
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'X-REQUEST-ID',
        'X-Requested-With',
        'Content-Type',
        'Accept',
        'Authorization',
        'Origin',
        'Access-Control-Allow-Origin',
    ],
    
    'exposed_headers' => [
        'Authorization',
        'Set-Cookie',
    ],
    
    'max_age' => 86400,
    
    'supports_credentials' => true, // Critical for CSRF and session cookies
];