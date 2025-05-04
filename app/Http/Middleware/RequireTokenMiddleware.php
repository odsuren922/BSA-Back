<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequireTokenMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Check multiple token sources
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            Log::warning('No authentication token found for API request', [
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
        }
        
        // Set token for downstream middleware
        $request->headers->set('Authorization', 'Bearer ' . $token);
        
        return $next($request);
    }
    
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
        
        // 3. Check for token in session or cookie
        $sessionToken = session('oauth_token') ?? $request->cookie('oauth_token');
        if ($sessionToken) {
            return $sessionToken;
        }
        
        // 4. Check for access_token parameter
        $token = $request->input('access_token');
        if ($token) {
            return $token;
        }
        
        return null;
    }
}