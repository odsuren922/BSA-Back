<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TokenAuthenticationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Try to get token from different sources
        $token = $this->getTokenFromRequest($request);
        
        if (!$token) {
            Log::warning('No authentication token found', [
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);
            
            return $this->handleUnauthenticated($request);
        }
        
        // Check if token is about to expire and refresh if needed
        $token = $this->handleTokenRefresh($token, $request);
        
        // Set token for downstream middleware
        $request->headers->set('Authorization', 'Bearer ' . $token);
        
        return $next($request);
    }
    
    protected function getTokenFromRequest(Request $request)
    {
        // 1. Try Authorization header
        $token = $request->bearerToken();
        if ($token) {
            return $token;
        }
        
        // 2. Try session
        $tokenData = session(config('oauth.token_session_key'));
        if ($tokenData && isset($tokenData['access_token'])) {
            return $tokenData['access_token'];
        }
        
        // 3. Try local storage via cookie (less secure)
        $token = $request->cookie('oauth_token');
        if ($token) {
            return $token;
        }
        
        return null;
    }
    
    protected function handleTokenRefresh($token, Request $request)
    {
        if (session()->has(config('oauth.token_session_key'))) {
            $tokenData = session(config('oauth.token_session_key'));
            
            if (isset($tokenData['expires_in']) && isset($tokenData['created_at'])) {
                $expiresAt = $tokenData['created_at'] + $tokenData['expires_in'];
                $buffer = config('oauth.token_refresh_buffer', 300);
                
                if (time() >= ($expiresAt - $buffer) && isset($tokenData['refresh_token'])) {
                    try {
                        $oauthService = app(\App\Services\OAuthService::class);
                        $newTokenData = $oauthService->refreshToken($tokenData['refresh_token']);
                        
                        if ($newTokenData && isset($newTokenData['access_token'])) {
                            $newTokenData['created_at'] = time();
                            session([config('oauth.token_session_key') => $newTokenData]);
                            
                            return $newTokenData['access_token'];
                        }
                    } catch (\Exception $e) {
                        Log::warning('Proactive token refresh failed: ' . $e->getMessage());
                    }
                }
            }
        }
        
        return $token;
    }
    
    protected function handleUnauthenticated(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please log in again.',
                'redirect' => '/login'
            ], 401);
        } else {
            return redirect()->route('login')
                ->with('error', 'Your session has expired. Please log in again.');
        }
    }
}