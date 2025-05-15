<?php

return [
    /*
    |--------------------------------------------------------------------------
    | HUB API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for connecting to the NUM University HUB API.
    |
    */

    // API endpoint for GraphQL queries
    'endpoint' => env('HUB_API_ENDPOINT', 'https://tree.num.edu.mn/gateway'),

    // Client credentials
    'client_id' => env('HUB_API_CLIENT_ID', ''),
    'client_secret' => env('HUB_API_CLIENT_SECRET', ''),

    // Whether to verify SSL certificates
    'verify_ssl' => env('HUB_API_VERIFY_SSL', false),

    // Default department ID to use if none specified
    'default_department' => env('HUBAPI_DEFAULT_DEPARTMENT', '1001298'),

    // Course settings
    'thesis_course' => env('HUBAPI_THESIS_COURSE', 'THES400'),

    // Academic year and semester settings
    'academic_year' => env('HUBAPI_ACADEMIC_YEAR', 2025),
    'semester' => env('HUBAPI_SEMESTER', 4), // 1 for Fall, 4 for Spring

    // Caching options
    'cache_enabled' => env('HUBAPI_CACHE_ENABLED', true),
    'cache_ttl' => env('HUBAPI_CACHE_TTL', 3600), // Cache for 1 hour
];