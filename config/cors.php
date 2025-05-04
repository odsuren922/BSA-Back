<?php

return [
    'paths' => ['api/*', 'oauth/*', 'sanctum/csrf-cookie', 'login', 'logout'],
    
    'allowed_origins' => ['http://localhost:4000'],
    
    'allowed_methods' => ['*'],
    
    'allowed_headers' => [
        'X-CSRF-TOKEN',
        'X-Requested-With',
        'Content-Type',
        'Accept',
        'Authorization',
        'Origin',
    ],
    
    'exposed_headers' => ['Authorization'],
    
    'max_age' => 0,
    
    'supports_credentials' => true, // Critical for CSRF and session cookies
];