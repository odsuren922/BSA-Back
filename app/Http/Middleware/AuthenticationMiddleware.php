<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TokenService;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Auth;
class AuthenticationMiddleware
{
    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }
    
    // public function handle(Request $request, Closure $next, $guard = null)
    // {
    //     // Generate a request ID for logging
    //     $requestId = $request->header('X-Request-ID') ?? substr(md5(uniqid()), 0, 8);
        
    //     // Log the authentication attempt for debugging
    //     Log::debug('Authentication middleware processing request', [
    //         'request_id' => $requestId,
    //         'path' => $request->path(),
    //         'method' => $request->method(),
    //         'has_bearer_token' => $request->bearerToken() ? true : false,
    //         'has_session_token' => session()->has(config('oauth.token_session_key')),
    //     ]);
        
    //     // Skip authentication for certain paths
    //     if ($this->shouldSkipAuthentication($request)) {
    //         return $next($request);
    //     }
        
    //     // Get token from Authorization header (primary method)
    //     $token = $request->bearerToken();
        
    //     // Fallback to session for web routes if no Authorization header
    //     if (!$token && session()->has(config('oauth.token_session_key'))) {
    //         $tokenData = session(config('oauth.token_session_key'));
    //         if (isset($tokenData['access_token'])) {
    //             $token = $tokenData['access_token'];
                
    //             // Set token in header for downstream middleware
    //             $request->headers->set('Authorization', 'Bearer ' . $token);
                
    //             Log::debug('Using token from session', [
    //                 'request_id' => $requestId,
    //                 'token_preview' => substr($token, 0, 10) . '...',
    //             ]);
    //         }
    //     }
        
    //     // Additional check for API token in request
    //     if (!$token && $request->input('access_token')) {
    //         $token = $request->input('access_token');
    //         $request->headers->set('Authorization', 'Bearer ' . $token);
            
    //         Log::debug('Using token from request parameter', [
    //             'request_id' => $requestId,
    //             'token_preview' => substr($token, 0, 10) . '...',
    //         ]);
    //     }
        
    //     // Proceed if token was found
    //     if ($token) {
    //         // Optionally validate token here if needed
            
    //         Log::debug('Token found, proceeding with request', [
    //             'request_id' => $requestId,
    //             'path' => $request->path(),
    //         ]);
            
    //         return $next($request);
    //     }
        
    //     // Handle unauthenticated user
    //     Log::warning('Authentication failed - no valid token found', [
    //         'request_id' => $requestId,
    //         'path' => $request->path(),
    //         'method' => $request->method(),
    //     ]);
        
    //     if ($request->expectsJson() || $request->is('api/*')) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Unauthenticated',
    //             'redirect' => '/login',
    //             'request_id' => $requestId,
    //             'timestamp' => now()->toDateTimeString(),
    //         ], 401);
    //     }
        
    //     return redirect()->route('oauth.redirect')
    //         ->with('error', 'Authentication required. Please log in.');
    // }
    public function handle(Request $request, Closure $next, $guard = null)
{
    $requestId = $request->header('X-Request-ID') ?? substr(md5(uniqid()), 0, 8);

    Log::debug('Authentication middleware processing request', [
        'request_id' => $requestId,
        'path' => $request->path(),
        'method' => $request->method(),
        'has_bearer_token' => $request->bearerToken() ? true : false,
        'has_session_token' => session()->has(config('oauth.token_session_key')),
    ]);

    if ($this->shouldSkipAuthentication($request)) {
        return $next($request);
    }

    $token = $request->bearerToken();

    if (!$token && session()->has(config('oauth.token_session_key'))) {
        $tokenData = session(config('oauth.token_session_key'));
        if (isset($tokenData['access_token'])) {
            $token = $tokenData['access_token'];
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }
    }

    if (!$token && $request->input('access_token')) {
        $token = $request->input('access_token');
        $request->headers->set('Authorization', 'Bearer ' . $token);
    }

    if ($token) {
        $user = $this->tokenService->getUserFromToken($token);

        if ($user) {
            Log::info("User resolved from token", [
                'id' => $user->id,
                'type' => get_class($user),
            ]);

            // ✅ request-д хэрэглэгчийг inject хийнэ
            $request->setUserResolver(fn () => $user);

            return $next($request);
        }

        Log::warning("Token found but user not resolved", [
            'token' => $token,
        ]);
    }

    Log::warning('Authentication failed - no valid token found', [
        'request_id' => $requestId,
        'path' => $request->path(),
        'method' => $request->method(),
    ]);

    return response()->json([
        'success' => false,
        'message' => 'Unauthenticated',
        'request_id' => $requestId,
        'timestamp' => now()->toDateTimeString(),
    ], 401);
}

    /**
     * Check if authentication should be skipped for the current route
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldSkipAuthentication(Request $request)
    {
        $path = $request->path();
        
        // Public routes that don't need authentication
        $publicPaths = [
            'login',
            'oauth/redirect',
            'oauth/callback',
            'auth',
            'api/oauth/exchange-token',
            'api/oauth/refresh-token',
            'sanctum/csrf-cookie',
        ];
        
        foreach ($publicPaths as $publicPath) {
            if (str_contains($path, $publicPath)) {
                return true;
            }
        }
        
        return false;
    }
}