<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use App\Models\Student;
use App\Models\Teacher;
class TokenService
{
    protected $oauthService;

    // Constructor функц: OAuthService-ийг дамжуулж хадгална.
    public function __construct(OAuthService $oauthService)
    {
        $this->oauthService = $oauthService;
    }

    /**
     * Request-оос токеныг авах функц.
     * 1. Authorization header-оос авахыг оролдоно.
     * 2. Олдохгүй бол session-оос авна.
     */
    public function getTokenFromRequest(Request $request)
    {
        $requestId = substr(md5(uniqid()), 0, 8);
        Log::debug("[Auth-{$requestId}] Токен авах оролдлого", [
            'path' => $request->path(),
            'ip' => $request->ip()
        ]);

        $token = $request->bearerToken();
        if ($token) {
            Log::debug("[Auth-{$requestId}] Header дотор токен олдсон");
            return $token;
        }

        $tokenData = session(config('oauth.token_session_key'));
        if ($tokenData && isset($tokenData['access_token'])) {
            Log::debug("[Auth-{$requestId}] Session дотор токен олдсон");
            return $tokenData['access_token'];
        }

        Log::debug("[Auth-{$requestId}] Токен олдсонгүй");
        return null;
    }

    /**
     * Токеныг өгөгдлийн санд хадгалах функц.
     *
     * @param array $data (user_id, access_token, refresh_token гэх мэт)
     * @return bool
     */
    public function storeTokenInDatabase($data)
    {
        try {
            if (!isset($data['user_id']) || !isset($data['access_token'])) {
                Log::error('Токен хадгалах боломжгүй: user_id эсвэл access_token дутуу байна.', $data);
                return false;
            }

            $expiresAt = date('Y-m-d H:i:s', ($data['created_at'] ?? time()) + ($data['expires_in'] ?? 3600));

            $existing = DB::table('user_tokens')
                ->where('user_id', $data['user_id'])
                ->where('user_type', $data['user_type'] ?? null)
                ->first();

            if ($existing) {
                DB::table('user_tokens')
                    ->where('user_id', $data['user_id'])
                    ->where('user_type', $data['user_type'] ?? null)
                    ->update([
                        'access_token' => $data['access_token'],
                        'refresh_token' => $data['refresh_token'] ?? null,
                        'expires_at' => $expiresAt,
                        'updated_at' => now()
                    ]);
            } else {
                DB::table('user_tokens')->insert([
                    'user_id' => $data['user_id'],
                    'user_type' => $data['user_type'] ?? null,
                    'access_token' => $data['access_token'],
                    'refresh_token' => $data['refresh_token'] ?? null,
                    'expires_at' => $expiresAt,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            return true;
        } catch (\Exception $e) {
            Log::error('Token хадгалах үед алдаа гарлаа', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Токеныг өгөгдлийн сангаас унших функц
     */
    public function getTokenFromDatabase($userId, $userType = null)
    {
        try {
            $query = DB::table('user_tokens')->where('user_id', $userId);
            if ($userType) {
                $query->where('user_type', $userType);
            }

            $record = $query->first();

            if (!$record) return null;

            if (strtotime($record->expires_at) <= time()) {
                Log::info('Токен хугацаа хэтэрсэн. Шинэчлэхийг оролдож байна.');
                if ($record->refresh_token) {
                    return $this->refreshTokenUsingRefreshToken($record->refresh_token);
                }
                return null;
            }

            return [
                'access_token' => $record->access_token,
                'refresh_token' => $record->refresh_token,
                'expires_in' => max(0, strtotime($record->expires_at) - time()),
                'created_at' => time() - (strtotime($record->expires_at) - time()),
            ];
        } catch (\Exception $e) {
            Log::error('Token авах үед алдаа гарлаа: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Refresh token ашиглан access token-ийг шинэчлэх
     */
    public function refreshTokenUsingRefreshToken($refreshToken)
    {
        try {
            $newData = $this->oauthService->refreshToken($refreshToken);
            if (!$newData || !isset($newData['access_token'])) return null;

            $newData['created_at'] = time();
            session([config('oauth.token_session_key') => $newData]);

            $userData = session('oauth_user');
            if ($userData && isset($userData['id'])) {
                $this->storeTokenInDatabase([
                    'user_id' => $userData['id'],
                    'user_type' => $userData['role'] ?? null,
                    'access_token' => $newData['access_token'],
                    'refresh_token' => $newData['refresh_token'] ?? null,
                    'expires_in' => $newData['expires_in'] ?? 3600,
                    'created_at' => $newData['created_at']
                ]);
            }

            return $newData;
        } catch (\Exception $e) {
            Log::error('Token шинэчлэхэд алдаа гарлаа: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Токены хугацаа хэтэрсэн эсэхийг шалгаж, шаардлагатай бол шинэчилнэ
     */
    public function refreshTokenIfNeeded($token, $refreshToken = null, $forceRefresh = false)
    {
        $tokenData = session(config('oauth.token_session_key'));
        if (!$tokenData) return null;

        if (!$refreshToken && isset($tokenData['refresh_token'])) {
            $refreshToken = $tokenData['refresh_token'];
        }

        $needsRefresh = $forceRefresh;
        if (!$needsRefresh && isset($tokenData['expires_in']) && isset($tokenData['created_at'])) {
            $expiresAt = $tokenData['created_at'] + $tokenData['expires_in'];
            $buffer = config('oauth.token_refresh_buffer', 300);
            $needsRefresh = time() >= ($expiresAt - $buffer);
        }

        if ($needsRefresh && $refreshToken) {
            return $this->refreshTokenUsingRefreshToken($refreshToken);
        }

        return null;
    }


    public function getUserFromToken(string $token): mixed
    {
        $record = DB::table('user_tokens')->where('access_token', $token)->first();
    
        if (!$record) {
            Log::warning("Token not found in DB: $token");
            return null;
        }
    
        return match ($record->user_type) {
            'student' => Student::find($record->user_id),
            'teacher' => Teacher::find($record->user_id),
            default => null,
        };
    }
    

}