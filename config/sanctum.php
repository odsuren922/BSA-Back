<?php

return [
    // The expiration time of the token in minutes
    'expiration' => 60 * 24, // 24 hours

    // Define which domains can receive your cookies
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:4000,127.0.0.1,127.0.0.1:4000,::1',
        env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    // Set same-site attribute for cookies
    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],

    'prefix' => 'sanctum',
];