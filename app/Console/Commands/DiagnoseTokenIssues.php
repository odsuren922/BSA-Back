<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\TokenService;
use App\Services\OAuthService;

class DiagnoseTokenIssues extends Command
{
    protected $signature = 'auth:diagnose {user_id?} {user_type?}';
    protected $description = 'Diagnose token-related issues for users';

    protected $tokenService;
    protected $oauthService;

    public function __construct(TokenService $tokenService, OAuthService $oauthService)
    {
        parent::__construct();
        $this->tokenService = $tokenService;
        $this->oauthService = $oauthService;
    }

    public function handle()
    {
        $this->info('Diagnosing token issues...');
        
        $userId = $this->argument('user_id');
        $userType = $this->argument('user_type');
        
        if ($userId) {
            $this->diagnoseSingleUser($userId, $userType);
        } else {
            $this->diagnoseAllUsers();
        }
        
        return 0;
    }
    
    protected function diagnoseSingleUser($userId, $userType = null)
    {
        $this->info("Diagnosing token issues for user: $userId");
        
        // Check user tokens table
        $tokenRecord = DB::table('user_tokens')
            ->where('user_id', $userId);
            
        if ($userType) {
            $tokenRecord->where('user_type', $userType);
        }
        
        $tokenRecord = $tokenRecord->first();
        
        if (!$tokenRecord) {
            $this->error("No token record found for user: $userId");
            return;
        }
        
        $this->info("Token record found:");
        $this->table(
            ['ID', 'User ID', 'User Type', 'Expires At', 'Created At', 'Updated At'],
            [[$tokenRecord->id, $tokenRecord->user_id, $tokenRecord->user_type, $tokenRecord->expires_at, $tokenRecord->created_at, $tokenRecord->updated_at]]
        );
        
        // Check token expiration
        $expiresAt = strtotime($tokenRecord->expires_at);
        $now = time();
        
        if ($expiresAt <= $now) {
            $this->warn("Token is expired! Expired at: " . date('Y-m-d H:i:s', $expiresAt));
            
            if ($tokenRecord->refresh_token) {
                $this->info("Refresh token is available. Attempting to refresh...");
                
                try {
                    $newTokenData = $this->oauthService->refreshToken($tokenRecord->refresh_token);
                    
                    if ($newTokenData && isset($newTokenData['access_token'])) {
                        $this->info("Token refreshed successfully!");
                        
                        // Store the new token
                        $this->tokenService->storeTokenInDatabase([
                            'user_id' => $tokenRecord->user_id,
                            'user_type' => $tokenRecord->user_type,
                            'access_token' => $newTokenData['access_token'],
                            'refresh_token' => $newTokenData['refresh_token'] ?? $tokenRecord->refresh_token,
                            'expires_in' => $newTokenData['expires_in'] ?? 3600,
                            'created_at' => time()
                        ]);
                        
                        $this->info("New token stored in database!");
                    } else {
                        $this->error("Failed to refresh token!");
                    }
                } catch (\Exception $e) {
                    $this->error("Error refreshing token: " . $e->getMessage());
                }
            } else {
                $this->error("No refresh token available!");
            }
        } else {
            $this->info("Token is valid! Expires at: " . date('Y-m-d H:i:s', $expiresAt));
            $this->info("Time remaining: " . ($expiresAt - $now) . " seconds");
        }
        
        // Check user sessions table
        $sessions = DB::table('user_sessions')
            ->where('user_id', $userId)
            ->get();
            
        if ($sessions->isEmpty()) {
            $this->warn("No session records found for user: $userId");
        } else {
            $this->info("Session records found: " . count($sessions));
            
            $sessionData = $sessions->map(function ($session) {
                return [
                    $session->id,
                    $session->user_id,
                    $session->user_type,
                    $session->ip_address,
                    substr($session->user_agent, 0, 30) . '...',
                    $session->last_active_at,
                    $session->created_at
                ];
            })->toArray();
            
            $this->table(
                ['ID', 'User ID', 'User Type', 'IP Address', 'User Agent', 'Last Active', 'Created At'],
                $sessionData
            );
        }
    }
    
    protected function diagnoseAllUsers()
    {
        $this->info("Diagnosing token issues for all users...");
        
        // Get all token records
        $tokens = DB::table('user_tokens')->get();
        
        if ($tokens->isEmpty()) {
            $this->error("No token records found in the database!");
            return;
        }
        
        $this->info("Total token records: " . count($tokens));
        
        // Count expired tokens
        $now = time();
        $expiredCount = 0;
        $validCount = 0;
        $refreshableCount = 0;
        
        foreach ($tokens as $token) {
            $expiresAt = strtotime($token->expires_at);
            
            if ($expiresAt <= $now) {
                $expiredCount++;
                
                if ($token->refresh_token) {
                    $refreshableCount++;
                }
            } else {
                $validCount++;
            }
        }
        
        $this->info("Token status summary:");
        $this->info("- Valid tokens: $validCount");
        $this->info("- Expired tokens: $expiredCount");
        $this->info("- Refreshable tokens: $refreshableCount");
        
        // Show top 5 most recent token records
        $this->info("Most recent token records:");
        
        $recentTokens = DB::table('user_tokens')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
            
        $tokenData = $recentTokens->map(function ($token) use ($now) {
            $expiresAt = strtotime($token->expires_at);
            $status = $expiresAt <= $now ? 'Expired' : 'Valid';
            
            return [
                $token->id,
                $token->user_id,
                $token->user_type,
                $status,
                $token->expires_at,
                $token->updated_at
            ];
        })->toArray();
        
        $this->table(
            ['ID', 'User ID', 'User Type', 'Status', 'Expires At', 'Updated At'],
            $tokenData
        );
        
        // Session statistics
        $sessions = DB::table('user_sessions')->get();
        
        if ($sessions->isEmpty()) {
            $this->warn("No session records found in the database!");
        } else {
            $this->info("Session statistics:");
            $this->info("Total sessions: " . count($sessions));
            
            // Count active sessions
            $activeThreshold = now()->subHours(1);
            $activeSessions = $sessions->filter(function ($session) use ($activeThreshold) {
                return $session->last_active_at >= $activeThreshold;
            });
            
            $this->info("Active sessions (last hour): " . count($activeSessions));
            
            // Show top 5 most recent sessions
            $this->info("Most recent sessions:");
            
            $recentSessions = $sessions->sortByDesc('last_active_at')->take(5);
            
            $sessionData = $recentSessions->map(function ($session) {
                return [
                    $session->id,
                    $session->user_id,
                    $session->user_type,
                    $session->ip_address,
                    substr($session->user_agent, 0, 30) . '...',
                    $session->last_active_at,
                    $session->created_at
                ];
            })->toArray();
            
            $this->table(
                ['ID', 'User ID', 'User Type', 'IP Address', 'User Agent', 'Last Active', 'Created At'],
                $sessionData
            );
        }
    }
}