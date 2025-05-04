<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OAuthService;
use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Department;
use App\Models\Supervisor;

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
            $cookie = cookie('oauth_state', $state, 5, '/', null, config('session.secure'), config('session.http_only'));
            
            // Build the authorization URL
            $authUrl = $this->oauthService->getAuthorizationUrl($state);
            
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
            
            return redirect()->route('login')->with('error', 
                'Authentication service is currently unavailable. Please try again later or contact support.');
        }
    }

    /**
     * Handle the callback from the OAuth provider
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleProviderCallback(Request $request)
    {
        Log::info('OAuth callback received', [
            'has_code' => $request->has('code'),
            'has_state' => $request->has('state'),
        ]);
        
        // Verify state parameter to prevent CSRF
        $savedState = $request->cookie('oauth_state');
        $returnedState = $request->input('state');
        
        if (!$savedState || $savedState !== $returnedState) {
            Log::warning('OAuth state mismatch', [
                'saved_state' => $savedState ? 'exists' : 'missing',
                'returned_state' => $returnedState ? 'exists' : 'missing',
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Invalid authentication state. Please try again.');
        }
        
        $code = $request->input('code');
        
        if (!$code) {
            Log::warning('OAuth callback received without code');
            return redirect()->route('login')
                ->with('error', 'Authentication failed. Please try again.');
        }
        
        try {
            // Exchange the code for access token
            $tokenData = $this->oauthService->getAccessToken($code);
            
            if (!$tokenData || !isset($tokenData['access_token'])) {
                Log::error('Failed to obtain access token');
                return redirect()->route('login')
                    ->with('error', 'Failed to obtain access token. Please try again.');
            }
            
            // Add creation timestamp for expiration tracking
            $tokenData['created_at'] = time();
            
            // Store token data in session
            session([config('oauth.token_session_key') => $tokenData]);
            
            // Get user data from API
            $userData = $this->oauthService->getUserData($tokenData['access_token']);
            
            if (!$userData) {
                Log::error('Failed to get user data with access token');
                return redirect()->route('login')
                    ->with('error', 'Failed to get user data. Please try again.');
            }
            
            // Extract user info from OAuth response
            $email = $this->findValueByType($userData, 'nummail') ?? $this->findValueByType($userData, 'email');
            $username = $this->findValueByType($userData, 'username');
            $gid = $this->findValueByType($userData, 'gid');
            $firstName = $this->findValueByType($userData, 'fnamem') ?? $this->findValueByType($userData, 'fname');
            $lastName = $this->findValueByType($userData, 'lnamem') ?? $this->findValueByType($userData, 'lname');
            
            // Determine role from GID
            $role = $this->roleService->mapGidToRole($gid);
            
            // Find the user in the database
            $user = $this->findUserInDatabase($email, $username, $role);
            
            if (!$user) {
                Log::warning('User from OAuth not found in database', [
                    'email' => $email,
                    'username' => $username,
                    'role' => $role
                ]);
                
                // Clear the session token
                session()->forget(config('oauth.token_session_key'));
                
                return redirect()->route('login')
                    ->with('error', 'Your account does not exist in our system. Please contact an administrator.');
            }
            
            // Store user data in session
            session(['user_data' => [
                'id' => $user['id'],
                'role' => $role,
                'email' => $email,
                'name' => $firstName . ' ' . $lastName,
                'username' => $username,
                'gid' => $gid,
            ]]);
            
            // Create Sanctum token for the user
            $this->createSanctumToken($user['model'], $role);
            
            // Record the new session
            $this->recordUserSession($request, $user['id'], $role);
            
            Log::info('User authenticated successfully', [
                'user_id' => $user['id'],
                'role' => $role,
                'email' => $email
            ]);
            
            // Redirect to the home page for the role
            return redirect()->intended('/');
        } catch (\Exception $e) {
            Log::error('OAuth callback error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Authentication error: ' . $e->getMessage());
        }
    }

    /**
     * Find user in our database based on OAuth data
     *
     * @param string|null $email
     * @param string|null $username
     * @param string $role
     * @return array|null
     */
    protected function findUserInDatabase($email, $username, $role)
    {
        // Check different tables based on role
        if ($role === 'student') {
            $student = Student::where('mail', $email)->first();
            
            if (!$student && $username) {
                // Try with username pattern for students (usually matches sisi_id)
                $student = Student::where('sisi_id', $username)->first();
            }
            
            if ($student) {
                return [
                    'id' => $student->id,
                    'type' => 'student',
                    'model' => $student
                ];
            }
        } elseif ($role === 'teacher') {
            $teacher = Teacher::where('mail', $email)->first();
            
            if ($teacher) {
                return [
                    'id' => $teacher->id,
                    'type' => 'teacher',
                    'model' => $teacher
                ];
            }
        } elseif ($role === 'supervisor') {
            $supervisor = Supervisor::where('mail', $email)->first();
            
            if ($supervisor) {
                return [
                    'id' => $supervisor->id,
                    'type' => 'supervisor',
                    'model' => $supervisor
                ];
            }
        } elseif ($role === 'department') {
            // Departments might have a different mapping logic
            // This is a placeholder implementation
            $department = Department::where('id', $username)->first();
            
            if ($department) {
                return [
                    'id' => $department->id,
                    'type' => 'department',
                    'model' => $department
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Create a Sanctum token for a user
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param string $role
     * @return string
     */
    protected function createSanctumToken($user, $role)
    {
        // Remove existing tokens for this user
        $user->tokens()->delete();
        
        // Create a new token with role-based abilities
        return $user->createToken('auth_token', [$role])->plainTextToken;
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
        try {
            DB::table('user_sessions')->insert([
                'user_id' => $userId,
                'user_type' => $userType,
                'ip_address' => $request->ip(),
                'user_agent' => substr($request->userAgent(), 0, 500),
                'last_active_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record user session: ' . $e->getMessage());
            // Don't interrupt the request flow if session tracking fails
        }
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
     * Exchange authorization code for tokens
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exchangeToken(Request $request)
    {
        Log::info('Token exchange request received', [
            'has_code' => $request->has('code'),
            'has_state' => $request->has('state'),
        ]);

        $code = $request->input('code');
        $state = $request->input('state');
        $redirectUri = $request->input('redirect_uri');
        
        if (!$code) {
            return response()->json(['error' => 'No authorization code provided'], 400);
        }
        
        try {
            // Exchange the code for access token
            Log::info('Exchanging authorization code for access token');
            $tokenData = $this->oauthService->getAccessToken($code, $state, $redirectUri);
            
            if (!$tokenData || !isset($tokenData['access_token'])) {
                Log::error('Failed to obtain access token', [
                    'token_data' => $tokenData ? 'exists but no access_token' : 'null',
                ]);
                
                return response()->json(['error' => 'Failed to obtain access token'], 401);
            }
            
            // Add creation timestamp for expiration tracking
            $tokenData['created_at'] = time();
            $tokenData['token_time'] = time();

            session([config('oauth.token_session_key') => $tokenData]);
            
            // Get user data to include in the response
            Log::info('Fetching user data using token', [
                'token' => $this->maskString($tokenData['access_token']),
            ]);
            
            $userData = $this->oauthService->getUserData($tokenData['access_token']);
            
            if (!$userData) {
                Log::error('Failed to get user data with access token');
                return response()->json(['error' => 'Failed to get user data'], 401);
            }
            
            // Extract user info from OAuth response
            $email = $this->findValueByType($userData, 'nummail') ?? $this->findValueByType($userData, 'email');
            $username = $this->findValueByType($userData, 'username');
            $uid = $this->findValueByType($userData, 'uid');
            $gid = $this->findValueByType($userData, 'gid');
            $firstName = $this->findValueByType($userData, 'fnamem') ?? $this->findValueByType($userData, 'fname');
            $lastName = $this->findValueByType($userData, 'lnamem') ?? $this->findValueByType($userData, 'lname');
            
            // Determine role from GID
            $role = $this->roleService->mapGidToRole($gid);
            
            // Find or identify the user based on email/username
            $user = $this->findUserInDatabase($email, $username, $role);
            
            if (!$user) {
                return response()->json(['error' => 'User not found in system'], 404);
            }
            
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
                'access_token' => $tokenData['access_token'],
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
     * Refresh an expired access token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshToken(Request $request)
    {
        $refreshToken = $request->input('refresh_token');
        
        if (!$refreshToken) {
            return response()->json(['error' => 'No refresh token provided'], 400);
        }
        
        try {
            // Refresh the token
            $tokenData = $this->oauthService->refreshToken($refreshToken);
            
            if (!$tokenData || !isset($tokenData['access_token'])) {
                Log::error('Failed to refresh token', [
                    'token_data' => $tokenData ? 'exists but no access_token' : 'null',
                ]);
                
                return response()->json(['error' => 'Failed to refresh token'], 401);
            }
            
            // Add creation timestamp for expiration tracking
            $tokenData['created_at'] = time();
            $tokenData['token_time'] = time();
            
            // Update session token if applicable
            if (session()->has(config('oauth.token_session_key'))) {
                session([config('oauth.token_session_key') => $tokenData]);
            }
            
            return response()->json([
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'expires_in' => $tokenData['expires_in'] ?? 3600,
                'token_time' => $tokenData['created_at']
            ]);
        } catch (\Exception $e) {
            Log::error('Token refresh error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json(['error' => 'Failed to refresh token: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Get authenticated user data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserData(Request $request)
    {
        try {
            // Log the request for debugging
            Log::info('User data request received', [
                'has_auth_header' => $request->hasHeader('Authorization'),
                'has_session_token' => session()->has(config('oauth.token_session_key')),
            ]);
            
            // Get token from session or Authorization header
            $token = null;
            
            if (session()->has(config('oauth.token_session_key'))) {
                $tokenData = session(config('oauth.token_session_key'));
                $token = $tokenData['access_token'] ?? null;
                Log::info('Using token from session');
            }
            
            if (!$token && $request->hasHeader('Authorization')) {
                // Extract token from Bearer header
                $authHeader = $request->header('Authorization');
                if (strpos($authHeader, 'Bearer ') === 0) {
                    $token = substr($authHeader, 7);
                    Log::info('Using token from Authorization header');
                }
            }
            
            if (!$token) {
                Log::warning('No authentication token found');
                return response()->json(['error' => 'No authentication token found'], 401);
            }
            
            // Fetch user data from OAuth service
            $userData = $this->oauthService->getUserData($token);
            
            if (!$userData) {
                Log::error('Failed to fetch user data from OAuth service');
                return response()->json(['error' => 'Failed to fetch user data'], 401);
            }
            
            // Extract user info from OAuth response
            $email = $this->findValueByType($userData, 'nummail') ?? $this->findValueByType($userData, 'email');
            $username = $this->findValueByType($userData, 'username');
            $gid = $this->findValueByType($userData, 'gid');
            $firstName = $this->findValueByType($userData, 'fnamem') ?? $this->findValueByType($userData, 'fname');
            $lastName = $this->findValueByType($userData, 'lnamem') ?? $this->findValueByType($userData, 'lname');
            
            // Determine role from GID
            $role = $this->roleService->mapGidToRole($gid);
            
            // Find the user in our database
            $user = $this->findUserInDatabase($email, $username, $role);
            
            if (!$user) {
                Log::warning('User not found in database', [
                    'email' => $email,
                    'username' => $username,
                    'role' => $role
                ]);
                return response()->json(['error' => 'User not found in system'], 404);
            }
            
            // Format user data for response
            $formattedUser = [
                'id' => $user['id'],
                'type' => $user['type'],
                'name' => $firstName . ' ' . $lastName,
                'email' => $email ?? "{$username}@num.edu.mn",
                'role' => $role,
                'gid' => $gid,
                'firstName' => $firstName,
                'lastName' => $lastName,
            ];
            
            Log::info('Successfully retrieved user data');
            
            return response()->json($formattedUser);
        } catch (\Exception $e) {
            Log::error('Get user data error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json(['error' => 'Failed to get user data: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * Logout the user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        try {
            // Get user data from session
            $userData = session('user_data');
            
            if ($userData) {
                // Record logout in session tracking
                DB::table('user_sessions')
                    ->where('user_id', $userData['id'])
                    ->where('user_type', $userData['role'])
                    ->where('ip_address', $request->ip())
                    ->where('user_agent', substr($request->userAgent(), 0, 500))
                    ->update([
                        'last_active_at' => now()
                    ]);
                    
                // If user was authenticated with Sanctum, revoke the token
                if ($request->user()) {
                    $request->user()->currentAccessToken()->delete();
                }
            }
            
            // Clear session data
            session()->forget(config('oauth.token_session_key'));
            session()->forget('user_data');
            session()->invalidate();
            session()->regenerateToken();
            
            return redirect()->route('login')
                ->with('success', 'You have been logged out successfully.');
        } catch (\Exception $e) {
            Log::error('Logout error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return redirect()->route('login');
        }
    }
    
    /**
     * Mask a string for safe logging (shows first 4 and last 4 chars only)
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