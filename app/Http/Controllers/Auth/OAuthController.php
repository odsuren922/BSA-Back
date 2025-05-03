<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OAuthService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\DB;

class OAuthController extends Controller
{
    protected $oauthService;
    protected $roleService;

    public function __construct(OAuthService $oauthService, RoleService $roleService = null)
    {
        $this->oauthService = $oauthService;
        $this->roleService = $roleService ?? new RoleService();
    }

    /**
     * Redirect the user to the OAuth authorization page
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirectToProvider()
    {
        try {
            // Generate and store a random state to prevent CSRF attacks
            $state = Str::random(40);
            
            // Store state in cookie rather than session
            $cookie = cookie('oauth_state', $state, 5, '/', null, false, false);
            
            // Build the authorization URL
            $params = [
                'client_id' => config('oauth.client_id'),
                'response_type' => 'code',
                'redirect_uri' => config('oauth.redirect_uri', 'http://localhost:4000/auth'),
                'state' => $state
            ];
            
            $authUrl = 'https://auth.num.edu.mn/oauth2/oauth/authorize?' . http_build_query($params);
            
            // Log the redirect for debugging
            Log::info('Redirecting to OAuth provider', [
                'auth_url' => preg_replace('/client_secret=[^&]+/', 'client_secret=REDACTED', $authUrl),
            ]);
            
            // Redirect to the authorization server
            return redirect()->away($authUrl)->withCookie($cookie);
        } catch (\Exception $e) {
            Log::error('Failed to redirect to OAuth provider: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return redirect()->route('home')->with('error', 
                'Authentication service is currently unavailable. Please try again later or contact support.');
        }
    }

    /**
     * Exchange authorization code for tokens
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exchangeCodeForToken(Request $request)
    {
        Log::info('Token exchange request received', [
            'has_code' => $request->has('code'),
            'has_state' => $request->has('state'),
        ]);

        $code = $request->input('code');
        
        if (!$code) {
            return response()->json(['error' => 'No authorization code provided'], 400);
        }
        
        try {
            // Exchange the code for access token
            Log::info('Exchanging authorization code for access token');
            $tokenData = $this->oauthService->getAccessToken($code);
            
            if (!$tokenData || !isset($tokenData['access_token'])) {
                Log::error('Failed to obtain access token', [
                    'token_data' => $tokenData ? 'exists but no access_token' : 'null',
                ]);
                
                return response()->json(['error' => 'Failed to obtain access token'], 401);
            }
            
            // Add creation timestamp for expiration tracking
            $tokenData['created_at'] = time();
            $tokenData['token_time'] = time();
            
            // Get user data to include in the response
            Log::info('Fetching user data using token', [
                'token' => $this->maskString($tokenData['access_token']),
            ]);
            
            $userData = $this->oauthService->getUserData($tokenData['access_token']);
            
            if (!$userData) {
                Log::error('Failed to get user data with access token');
                return response()->json(['error' => 'Failed to get user data'], 401);
            }
            
            Log::info('Successfully fetched user data', [
                'data_count' => count($userData),
                'data_sample' => json_encode(array_slice($userData, 0, 1)),
            ]);
            
            // Extract user info from OAuth response
            $email = $this->findValueByType($userData, 'email');
            $username = $this->findValueByType($userData, 'username');
            $uid = $this->findValueByType($userData, 'uid');
            $gid = $this->findValueByType($userData, 'gid');
            $firstName = $this->findValueByType($userData, 'fnamem');
            $lastName = $this->findValueByType($userData, 'lnamem');
            
            // Determine role from GID
            $role = $this->roleService->mapGidToRole($gid);
            
            // Find or identify the user based on email/username
            $user = $this->findUserByEmail($email ?? "{$username}@num.edu.mn", $role);
            
            if (!$user) {
                return response()->json(['error' => 'User not found in system'], 404);
            }
            
            // Create a Sanctum token
            $token = $this->createTokenForUser($user['id'], $role);
            
            // Record the user session
            $this->recordUserSession($request, $user['id'], $role);
            
            // Format user data for response
            $formattedUser = [
                'id' => $user['id'],
                'name' => $firstName . ' ' . $lastName,
                'email' => $email ?? "{$username}@num.edu.mn",
                'role' => $role,
                'gid' => $gid,
                'fnamem' => $firstName,
                'lnamem' => $lastName,
            ];
            
            Log::info('Token exchange successful', [
                'has_user_data' => !empty($formattedUser),
                'token_type' => $tokenData['token_type'] ?? 'unknown',
            ]);
            
            return response()->json([
                'access_token' => $token,
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'expires_in' => $tokenData['expires_in'] ?? 3600,
                'token_time' => $tokenData['created_at'],
                'user' => $formattedUser
            ]);
        } catch (\Exception $e) {
            Log::error('Token exchange error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json(['error' => 'Failed to exchange code for token: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Find a user by email in the appropriate table based on role
     *
     * @param string $email
     * @param string $role
     * @return array|null
     */
    protected function findUserByEmail($email, $role)
    {
        // Identify which table to look in based on role
        $table = $this->getTableForRole($role);
        
        // Find the user
        $user = DB::table($table)
            ->where('mail', $email)
            ->first();
        
        if (!$user && strpos($email, '@') !== false) {
            // Try finding by username part
            $username = explode('@', $email)[0];
            $user = DB::table($table)
                ->where('id', $username)
                ->orWhere('sisi_id', $username)
                ->first();
        }
        
        if (!$user) {
            return null;
        }
        
        return (array)$user;
    }
    
    /**
     * Get the appropriate table name for a user role
     *
     * @param string $role
     * @return string
     */
    protected function getTableForRole($role)
    {
        $tableMappings = [
            'student' => 'students',
            'teacher' => 'teachers',
            'department' => 'departments',
            'supervisor' => 'supervisors',
        ];
        
        return $tableMappings[$role] ?? 'students';
    }
    
    /**
     * Create a Sanctum token for a user
     *
     * @param string $userId
     * @param string $role
     * @return string
     */
    protected function createTokenForUser($userId, $role)
    {
        // Generate a random string for the token
        $token = Str::random(80);
        
        // Hash the token and store it
        $hashedToken = hash('sha256', $token);
        
        // Store the token in the personal_access_tokens table
        DB::table('personal_access_tokens')->insert([
            'tokenable_type' => $role,
            'tokenable_id' => $userId,
            'name' => 'auth-token',
            'token' => $hashedToken,
            'abilities' => json_encode(['*']),
            'created_at' => now(),
            'updated_at' => now(),
            'expires_at' => now()->addDay(), // Token expires in 1 day
        ]);
        
        return $token;
    }
    
    /**
     * Record a user session
     *
     * @param Request $request
     * @param string $userId
     * @param string $userType
     * @return void
     */
    protected function recordUserSession(Request $request, $userId, $userType)
    {
        DB::table('user_sessions')->insert([
            'user_id' => $userId,
            'user_type' => $userType,
            'ip_address' => $request->ip(),
            'user_agent' => substr($request->userAgent(), 0, 500),
            'last_active_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    
    /**
     * Find a value in the user data array by its type
     *
     * @param array $userData
     * @param string $type
     * @return string|null
     */
    protected function findValueByType($userData, $type)
    {
        foreach ($userData as $item) {
            if (isset($item['Type']) && $item['Type'] === $type && isset($item['Value'])) {
                return $item['Value'];
            }
        }
        
        return null;
    }
    
    /**
     * Mask a string for safe logging
     *
     * @param string $string
     * @return string
     */
    protected function maskString($string)
    {
        $length = strlen($string);
        if ($length <= 8) {
            return '****';
        }
        
        return substr($string, 0, 4) . str_repeat('*', $length - 8) . substr($string, -4);
    }
}