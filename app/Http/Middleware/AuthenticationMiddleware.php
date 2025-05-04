<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TokenService;

class AuthenticationMiddleware
{
    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }






    
    public function handle(Request $request, Closure $next, $guard = null)
    {
        // Generate a request ID for logging
        $requestId = $request->header('X-Request-ID') ?? substr(md5(uniqid()), 0, 8);
        
        // Skip authentication for certain paths
        if ($this->shouldSkipAuthentication($request)) {
            return $next($request);
        }
        
        // Get token from Authorization header (primary method)
        $token = $request->bearerToken();
        
        // Fallback to session for web routes if no Authorization header
        if (!$token && session()->has(config('oauth.token_session_key'))) {
            $tokenData = session(config('oauth.token_session_key'));
            if (isset($tokenData['access_token'])) {
                $token = $tokenData['access_token'];
                
                // Set token in header for downstream middleware
                $request->headers->set('Authorization', 'Bearer ' . $token);
            }
        }
        
        // Proceed if token was found, otherwise redirect to login
        if ($token) {
            return $next($request);
        }
        
        // Handle unauthenticated user
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'redirect' => '/login',
                'request_id' => $requestId
            ], 401);
        }
        
        return redirect()->route('oauth.redirect')
            ->with('error', 'Authentication required. Please log in.');
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
    
    /**
     * Handle unauthenticated user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    protected function handleUnauthenticated(Request $request)
    {
        // Store intended URL if it's a GET request
        if ($request->isMethod('get') && !$request->expectsJson() && !$this->isAuthRoute($request)) {
            session()->put('url.intended', $request->url());
            Log::debug('Storing intended URL', [
                'url' => $request->url(),
                'time' => now()->toDateTimeString()
            ]);
        }
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'redirect' => '/login',
                'time' => now()->toDateTimeString()
            ], 401);
        }
        
        Log::debug('Redirecting unauthenticated user to OAuth login', [
            'time' => now()->toDateTimeString()
        ]);
        
        return redirect()->route('oauth.redirect')
            ->with('error', 'Your session has expired. Please log in again.');
    }
    
    /**
     * Check if the current route is an auth route
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function isAuthRoute(Request $request)
    {
        $path = $request->path();
        $authRoutes = ['login', 'oauth/redirect', 'oauth/callback', 'auth'];
        
        foreach ($authRoutes as $route) {
            if (strpos($path, $route) !== false) {
                return true;
            }
        }
        
        return false;
    }
}