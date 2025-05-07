<?php

return [
    /*
    |--------------------------------------------------------------------------
    | МУИС HUB API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for connecting to the МУИС HUB API
    | and retrieving data for the thesis management system.
    |
    */

    // API endpoint for GraphQL queries
    'endpoint' => env('HUBAPI_ENDPOINT', 'https://tree.num.edu.mn/gateway'),

    // Client credentials
    'client_id' => env('HUBAPI_CLIENT_ID', ''),
    'client_secret' => env('HUBAPI_CLIENT_SECRET', ''),

    // Whether to verify SSL certificates
    'verify_ssl' => env('HUBAPI_VERIFY_SSL', false),

    // Default department ID to use if none specified
    'departments' => [
        'default' => env('HUBAPI_DEFAULT_DEPARTMENT', 'MCST'), // Default to МКУТ
    ],

    // Course settings
    'courses' => [
        'thesis_code' => env('HUBAPI_THESIS_COURSE', 'THES400'), // Бакалаврын судалгааны ажил
    ],

    // Academic year and semester settings
    'academic_year' => env('HUBAPI_ACADEMIC_YEAR', 2025),
    'semester' => env('HUBAPI_SEMESTER', 4), // 1 for Fall, 4 for Spring

    // Caching options
    'cache' => [
        'enabled' => env('HUBAPI_CACHE_ENABLED', true),
        'ttl' => env('HUBAPI_CACHE_TTL', 3600), // Cache for 1 hour
    ],
];