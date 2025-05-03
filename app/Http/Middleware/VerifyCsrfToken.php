<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

/**
 * Class VerifyCsrfToken.
 */
class VerifyCsrfToken extends Middleware
{
    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        // OAuth routes
        'oauth/*',
        'api/oauth/*',
        'sanctum/csrf-cookie',
        'api/oauth/exchange-token',
        'oauth/exchange-token',
        'api/oauth/refresh-token',
        'api/oauth/token',

        
        // API routes
        'api/*',
        
        // Other excluded routes
        '2fa/*',
        'debugbar/*',
        'admin/*',
        'department/*',
        'impersonate/*',
        'livewire/*',
        'proposalform/*',
        'protected-page/*',
        'sanctum/*',
        'teacher/*',
        'student/*',
        'topic-response/*',
        'topic-requests/*',
        'topic-requestsbyteacher/*',
        'topic_confirm/*',
        'topic_decline/*',
        'topic/*',
        'topic_confirmed/*',
        'topic_requests_teacher/*',
        'topics/*',
        'topics_confirmed/*',
    ];
}