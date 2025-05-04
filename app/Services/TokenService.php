<?php
// app/Services/TokenService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;

class TokenService
{
    protected $oauthService;

    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    /**
     * Get token from various sources in the request
     *
     * @param Request $request
     * @return string|null
     */
    public function getTokenFromRequest(Request $request)
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
        
        return null;
    }

    /**
     * Refresh an access token if needed
     *
     * @param string $token Current token
     * @param string|null $refreshToken Refresh token (if available)
     * @param bool $forceRefresh Force refresh even if not expired
     * @return array|null New token data or null on failure
     */
    public function refreshTokenIfNeeded($token, $refreshToken = null, $forceRefresh = false)
    {
        // Check if token data is in session
        $tokenData = session(config('oauth.token_session_key'));
        
        if (!$tokenData) {
            return null;
        }
        
        // Use refresh token from session if not provided
        if (!$refreshToken && isset($tokenData['refresh_token'])) {
            $refreshToken = $tokenData['refresh_token'];
        }
        
        // Check if token needs refresh
        $needsRefresh = $forceRefresh;
        
        if (!$needsRefresh && isset($tokenData['expires_in']) && isset($tokenData['created_at'])) {
            $expiresAt = $tokenData['created_at'] + $tokenData['expires_in'];
            $buffer = config('oauth.token_refresh_buffer', 300);
            
            // Refresh if token will expire soon
            $needsRefresh = time() >= ($expiresAt - $buffer);
        }
        
        if ($needsRefresh && $refreshToken) {
            try {
                Log::info('Refreshing access token');
                $newTokenData = $this->oauthService->refreshToken($refreshToken);
                
                if ($newTokenData && isset($newTokenData['access_token'])) {
                    // Add creation timestamp for expiration tracking
                    $newTokenData['created_at'] = time();
                    
                    // Update token in session
                    session([config('oauth.token_session_key') => $newTokenData]);
                    
                    // Store token in database
                    $this->storeTokenInDatabase($newTokenData);
                    
                    Log::info('Token refreshed successfully');
                    return $newTokenData;
                } else {
                    Log::error('Failed to refresh token - invalid response');
                }
            } catch (\Exception $e) {
                Log::error('Token refresh failed: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        return null;
    }
    
    /**
     * Store token data in database
     *
     * @param array $tokenData
     * @return bool
     */
    public function storeTokenInDatabase($tokenData)
    {
        try {
            // Get current user ID (if available)
            $userId = null;
            $userType = null;
            
            // Try to get user data from session
            $userData = session('oauth_user');
            if ($userData && isset($userData['id'])) {
                $userId = $userData['id'];
                $userType = $userData['role'] ?? null;
            }
            
            if (!$userId) {
                Log::warning('Cannot store token - no user ID available');
                return false;
            }
            
            // Store or update token in database
            \Illuminate\Support\Facades\DB::table('user_tokens')
                ->updateOrInsert(
                    ['user_id' => $userId, 'user_type' => $userType],
                    [
                        'access_token' => $tokenData['access_token'],
                        'refresh_token' => $tokenData['refresh_token'] ?? null,
                        'expires_at' => date('Y-m-d H:i:s', ($tokenData['created_at'] ?? time()) + ($tokenData['expires_in'] ?? 3600)),
                        'updated_at' => now(),
                    ]
                );
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to store token in database: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get token data from database
     *
     * @param string $userId
     * @param string|null $userType
     * @return array|null
     */
    public function getTokenFromDatabase($userId, $userType = null)
    {
        try {
            $query = \Illuminate\Support\Facades\DB::table('user_tokens')
                ->where('user_id', $userId);
                
            if ($userType) {
                $query->where('user_type', $userType);
            }
            
            $tokenRecord = $query->first();
            
            if (!$tokenRecord) {
                return null;
            }
            
            // Check if token is expired
            if (strtotime($tokenRecord->expires_at) <= time()) {
                Log::info('Token from database is expired');
                
                if ($tokenRecord->refresh_token) {
                    // Try to refresh the token
                    return $this->refreshTokenIfNeeded(
                        $tokenRecord->access_token,
                        $tokenRecord->refresh_token,
                        true
                    );
                }
                
                return null;
            }
            
            // Format token data for consistency
            return [
                'access_token' => $tokenRecord->access_token,
                'refresh_token' => $tokenRecord->refresh_token,
                'expires_in' => max(0, strtotime($tokenRecord->expires_at) - time()),
                'created_at' => time() - (strtotime($tokenRecord->expires_at) - time()),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get token from database: ' . $e->getMessage());
            return null;
        }
    }
}