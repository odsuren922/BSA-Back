<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequireTokenMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check all possible token sources
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            Log::warning('No token found for API request', [
                'path' => $request->path(),
                'ip' => $request->ip(),
                'session_id' => session()->getId(),
                'has_session' => session()->has(config('oauth.token_session_key')),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }
        
        // Set the token for downstream middleware
        $request->headers->set('Authorization', 'Bearer ' . $token);
        
        return $next($request);
    }
    
    /**
     * Get token from various sources in the request
     * 
     * @param Request $request
     * @return string|null
     */
    protected function getTokenFromRequest(Request $request)
    {
        // 1. Check Authorization header
        $token = $request->bearerToken();
        if ($token) {
            return $token;
        }
        
        // 2. Check session for OAuth token
        $tokenData = session(config('oauth.token_session_key'));
        if ($tokenData && isset($tokenData['access_token'])) {
            return $tokenData['access_token'];
        }
        
        // 3. Check for Sanctum token in session
        $sanctumToken = session('sanctum_token');
        if ($sanctumToken) {
            return $sanctumToken;
        }
        
        // 4. Check for access_token parameter
        $token = $request->input('access_token');
        if ($token) {
            return $token;
        }
        
        return null;
    }
}