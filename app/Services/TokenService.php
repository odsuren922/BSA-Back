<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
        Log::debug('Attempting to get token from request', [
            'has_auth_header' => $request->hasHeader('Authorization'),
            'has_session' => session()->has(config('oauth.token_session_key')),
            'time' => now()->toDateTimeString()
        ]);
        
        // 1. Try Authorization header
        $token = $request->bearerToken();
        if ($token) {
            Log::debug('Found token in Authorization header');
            return $token;
        }
        
        // 2. Try session
        $tokenData = session(config('oauth.token_session_key'));
        if ($tokenData && isset($tokenData['access_token'])) {
            Log::debug('Found token in session');
            return $tokenData['access_token'];
        }
        
        // 3. Try cookie
        $token = $request->cookie('oauth_token');
        if ($token) {
            Log::debug('Found token in cookie');
            return $token;
        }
        
        // 4. Try database based on user ID in session
        $userData = session('oauth_user');
        if ($userData && isset($userData['id']) && isset($userData['role'])) {
            $tokenData = $this->getTokenFromDatabase($userData['id'], $userData['role']);
            if ($tokenData && isset($tokenData['access_token'])) {
                Log::debug('Found token in database');
                return $tokenData['access_token'];
            }
        }
        
        Log::debug('No token found in request');
        return null;
    }

    /**
     * Store token data in database
     *
     * @param array $tokenData
     * @return bool
     */
    public function storeTokenInDatabase($data)
    {
        try {
            if (!isset($data['user_id']) || !isset($data['access_token'])) {
                Log::error('Cannot store token - missing required data', [
                    'has_user_id' => isset($data['user_id']),
                    'has_access_token' => isset($data['access_token'])
                ]);
                return false;
            }
            
            $expiresAt = date('Y-m-d H:i:s', ($data['created_at'] ?? time()) + ($data['expires_in'] ?? 3600));
            
            Log::info('Storing token in database', [
                'user_id' => $data['user_id'],
                'user_type' => $data['user_type'] ?? null,
                'expires_at' => $expiresAt,
                'time' => now()->toDateTimeString()
            ]);
            
            // Store or update token in database
            DB::table('user_tokens')
                ->updateOrInsert(
                    ['user_id' => $data['user_id'], 'user_type' => $data['user_type'] ?? null],
                    [
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'] ?? null,
                        'expires_at' => $expiresAt,
                        'updated_at' => now(),
                        'created_at' => DB::raw('CASE WHEN created_at IS NULL THEN NOW() ELSE created_at END'),
                    ]
                );
            
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to store token in database: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'time' => now()->toDateTimeString()
            ]);
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
            Log::debug('Getting token from database', [
                'user_id' => $userId,
                'user_type' => $userType,
                'time' => now()->toDateTimeString()
            ]);
            
            $query = DB::table('user_tokens')
                ->where('user_id', $userId);
                
            if ($userType) {
                $query->where('user_type', $userType);
            }
            
            $tokenRecord = $query->first();
            
            if (!$tokenRecord) {
                Log::debug('No token found in database');
                return null;
            }
            
            Log::debug('Token found in database', [
                'expires_at' => $tokenRecord->expires_at,
                'has_refresh_token' => !empty($tokenRecord->refresh_token),
                'time' => now()->toDateTimeString()
            ]);
            
            // Check if token is expired
            if (strtotime($tokenRecord->expires_at) <= time()) {
                Log::info('Token from database is expired');
                
                if ($tokenRecord->refresh_token) {
                    // Try to refresh the token
                    $newTokenData = $this->refreshTokenUsingRefreshToken($tokenRecord->refresh_token);
                    if ($newTokenData) {
                        return $newTokenData;
                    }
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
            Log::error('Failed to get token from database: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'time' => now()->toDateTimeString()
            ]);
            return null;
        }
    }
    
    /**
     * Refresh token using refresh token
     *
     * @param string $refreshToken
     * @return array|null
     */
    public function refreshTokenUsingRefreshToken($refreshToken)
    {
        try {
            Log::info('Refreshing token using refresh token', [
                'time' => now()->toDateTimeString()
            ]);
            
            $newTokenData = $this->oauthService->refreshToken($refreshToken);
            
            if (!$newTokenData || !isset($newTokenData['access_token'])) {
                Log::error('Failed to refresh token - invalid response', [
                    'time' => now()->toDateTimeString()
                ]);
                return null;
            }
            
            // Add creation timestamp for expiration tracking
            $newTokenData['created_at'] = time();
            
            // Update token in session
            session([config('oauth.token_session_key') => $newTokenData]);
            
            // Get user data from session
            $userData = session('oauth_user');
            if ($userData && isset($userData['id'])) {
                // Store token in database
                $this->storeTokenInDatabase([
                    'user_id' => $userData['id'],
                    'user_type' => $userData['role'] ?? null,
                    'access_token' => $newTokenData['access_token'],
                    'refresh_token' => $newTokenData['refresh_token'] ?? null,
                    'expires_in' => $newTokenData['expires_in'] ?? 3600,
                    'created_at' => $newTokenData['created_at']
                ]);
            }
            
            Log::info('Token refreshed successfully', [
                'time' => now()->toDateTimeString()
            ]);
            
            return $newTokenData;
        } catch (\Exception $e) {
            Log::error('Token refresh failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'time' => now()->toDateTimeString()
            ]);
            return null;
        }
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
            Log::debug('No token data found in session for refresh check');
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
            
            Log::debug('Token refresh check', [
                'expires_at' => date('Y-m-d H:i:s', $expiresAt),
                'current_time' => date('Y-m-d H:i:s', time()),
                'buffer' => $buffer,
                'needs_refresh' => $needsRefresh,
                'time' => now()->toDateTimeString()
            ]);
        }
        
        if ($needsRefresh && $refreshToken) {
            return $this->refreshTokenUsingRefreshToken($refreshToken);
        }
        
        return null;
    }
}   