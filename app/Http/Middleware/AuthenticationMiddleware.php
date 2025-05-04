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

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        try {
            // Get token from request
            $token = $this->tokenService->getTokenFromRequest($request);
            
            if (!$token) {
                Log::warning('No authentication token found', [
                    'path' => $request->path(),
                    'ip' => $request->ip(),
                ]);
                
                return $this->handleUnauthenticated($request);
            }
            
            // Check if token data is in session
            $tokenData = session(config('oauth.token_session_key'));
            
            // Try to refresh token if needed
            if ($tokenData) {
                $newTokenData = $this->tokenService->refreshTokenIfNeeded($token);
                
                if ($newTokenData) {
                    // Update token in request for downstream middleware
                    $request->headers->set('Authorization', 'Bearer ' . $newTokenData['access_token']);
                    
                    // Update cookies if needed
                    if (config('oauth.use_cookies', false)) {
                        $cookie = cookie(
                            'oauth_token', 
                            $newTokenData['access_token'], 
                            config('oauth.cookie_lifetime', 60), 
                            '/', 
                            null, 
                            config('app.env') === 'production', 
                            true // HttpOnly
                        );
                        
                        return $next($request)->withCookie($cookie);
                    }
                }
            }
            
            // If all is well, proceed with the request
            return $next($request);
        } catch (\Exception $e) {
            Log::error('Authentication middleware error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->handleUnauthenticated($request);
        }
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
        }
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
                'redirect' => '/login'
            ], 401);
        }
        
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