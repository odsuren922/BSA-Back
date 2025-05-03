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
        // First check for token in Authorization header (Sanctum token or OAuth Bearer token)
        $token = $request->bearerToken();
        
        // If no token in header, check for token in session (OAuth flow)
        if (!$token) {
            $tokenData = session(config('oauth.token_session_key'));
            
            if (!$tokenData || !isset($tokenData['access_token'])) {
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
            
            // Set the token from session for downstream middleware
            $token = $tokenData['access_token'];
            $request->headers->set('Authorization', 'Bearer ' . $token);
        }
        
        // Check if token is about to expire and refresh if needed
        if (session()->has(config('oauth.token_session_key'))) {
            $tokenData = session(config('oauth.token_session_key'));
            
            if (isset($tokenData['expires_in']) && isset($tokenData['created_at'])) {
                $expiresAt = $tokenData['created_at'] + $tokenData['expires_in'];
                $buffer = config('oauth.token_refresh_buffer', 300); // Default 5 minutes buffer
                
                // If token expires within buffer time, try to refresh
                if (time() >= ($expiresAt - $buffer) && isset($tokenData['refresh_token'])) {
                    try {
                        $oauthService = app(\App\Services\OAuthService::class);
                        $newTokenData = $oauthService->refreshToken($tokenData['refresh_token']);
                        
                        if ($newTokenData && isset($newTokenData['access_token'])) {
                            $newTokenData['created_at'] = time();
                            session([config('oauth.token_session_key') => $newTokenData]);
                            
                            // Update the token in the request
                            $request->headers->set('Authorization', 'Bearer ' . $newTokenData['access_token']);
                            
                            Log::info('Token refreshed proactively');
                        }
                    } catch (\Exception $e) {
                        Log::warning('Proactive token refresh failed: ' . $e->getMessage());
                        // Continue with existing token
                    }
                }
            }
        }
        
        return $next($request);
    }
}