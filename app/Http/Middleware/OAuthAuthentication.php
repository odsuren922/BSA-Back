<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OAuthAuthentication
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
        // Check if we have user data in session (most reliable)
        if (session()->has('oauth_user')) {
            Log::info('User authenticated via session data');
            return $next($request);
        }
        
        // Check if token exists in session
        $tokenData = session(config('oauth.token_session_key'));
        
        // No token in session, check if there's an Authorization header
        if (!$tokenData && $request->bearerToken()) {
            Log::info('Using token from Authorization header');
            // Set the token in the request for downstream middleware
            $request->headers->set('Authorization', 'Bearer ' . $request->bearerToken());
            return $next($request);
        }
        
        if (!$tokenData || !isset($tokenData['access_token'])) {
            Log::warning('No OAuth token found in session or Authorization header', [
                'session_id' => session()->getId(),
                'path' => $request->path()
            ]);
            
            // Redirect to login instead of returning JSON for web routes
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please log in again.',
                    'redirect' => '/login'
                ], 401);
            } else {
                return redirect()->route('oauth.redirect')->with('error', 'Your session has expired. Please log in again.');
            }
        }
        
        try {
            // Validate if token is expired
            if (isset($tokenData['expires_in']) && isset($tokenData['created_at'])) {
                $expiresAt = $tokenData['created_at'] + $tokenData['expires_in'];
                
                if (time() >= $expiresAt) {
                    Log::info('Token has expired, attempting to refresh');
                    
                    if (isset($tokenData['refresh_token'])) {
                        $oauthService = app(\App\Services\OAuthService::class);
                        $newTokenData = $oauthService->refreshToken($tokenData['refresh_token']);
                        
                        if ($newTokenData && isset($newTokenData['access_token'])) {
                            $newTokenData['created_at'] = time();
                            session([config('oauth.token_session_key') => $newTokenData]);
                            $tokenData = $newTokenData;
                            Log::info('Token refreshed successfully');
                            
                            // Update Authorization header for downstream middleware
                            $request->headers->set('Authorization', 'Bearer ' . $newTokenData['access_token']);
                        } else {
                            Log::error('Token refresh failed');
                            
                            if ($request->expectsJson()) {
                                return response()->json([
                                    'success' => false, 
                                    'message' => 'Session expired. Please log in again.',
                                    'redirect' => '/login'
                                ], 401);
                            } else {
                                return redirect()->route('oauth.redirect')->with('error', 'Your session has expired. Please log in again.');
                            }
                        }
                    } else {
                        Log::error('No refresh token available');
                        
                        if ($request->expectsJson()) {
                            return response()->json([
                                'success' => false, 
                                'message' => 'Session expired. Please log in again.',
                                'redirect' => '/login'
                            ], 401);
                        } else {
                            return redirect()->route('oauth.redirect')->with('error', 'Your session has expired. Please log in again.');
                        }
                    }
                } else {
                    // Token is valid, set Authorization header for downstream middleware
                    $request->headers->set('Authorization', 'Bearer ' . $tokenData['access_token']);
                }
            }
            
            return $next($request);
            
        } catch (\Exception $e) {
            Log::error('OAuth middleware error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Authentication error. Please log in again.',
                    'redirect' => '/login'
                ], 401);
            } else {
                return redirect()->route('oauth.redirect')->with('error', 'Authentication error occurred. Please log in again.');
            }
        }
    }
}