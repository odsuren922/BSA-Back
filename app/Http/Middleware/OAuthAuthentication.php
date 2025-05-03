<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Department;
use App\Models\Supervisor;

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
        // Check if token exists in session
        $tokenData = session(config('oauth.token_session_key'));
        
        if (!$tokenData || !isset($tokenData['access_token'])) {
            Log::warning('No OAuth token found in session', [
                'session_id' => session()->getId(),
                'path' => $request->path()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please log in again.',
                'redirect' => '/login'
            ], 401);
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
                        } else {
                            Log::error('Token refresh failed');
                            return response()->json([
                                'success' => false, 
                                'message' => 'Session expired. Please log in again.',
                                'redirect' => '/login'
                            ], 401);
                        }
                    } else {
                        Log::error('No refresh token available');
                        return response()->json([
                            'success' => false, 
                            'message' => 'Session expired. Please log in again.',
                            'redirect' => '/login'
                        ], 401);
                    }
                }
            }
            
            // Get user data from OAuth service
            $oauthService = app(\App\Services\OAuthService::class);
            $userData = $oauthService->getUserData($tokenData['access_token']);
            
            if (!$userData) {
                Log::error('Failed to fetch user data from OAuth service');
                return response()->json([
                    'success' => false, 
                    'message' => 'Unable to fetch user data. Please log in again.',
                    'redirect' => '/login'
                ], 401);
            }
            
            // Extract key information
            $email = $this->findValueByType($userData, 'nummail') ?? $this->findValueByType($userData, 'email');
            $gid = $this->findValueByType($userData, 'gid');
            $username = $this->findValueByType($userData, 'username');
            
            // Validate user exists in our database
            $user = $this->findUserInDatabase($email, $username, $gid);
            
            if (!$user) {
                Log::warning('User from OAuth not found in database', [
                    'email' => $email,
                    'username' => $username,
                    'gid' => $gid
                ]);
                
                return response()->json([
                    'success' => false, 
                    'message' => 'Your account does not exist in our system.',
                    'redirect' => '/login'
                ], 403);
            }
            
            // Add user to request for downstream use
            $request->attributes->add(['user' => $user]);
            
            // Track user session
            $this->trackUserSession($request, $user);
            
            return $next($request);
            
        } catch (\Exception $e) {
            Log::error('OAuth middleware error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false, 
                'message' => 'Authentication error. Please log in again.',
                'redirect' => '/login'
            ], 500);
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
     * Find user in our database based on OAuth data
     *
     * @param string|null $email
     * @param string|null $username
     * @param string|null $gid
     * @return array|null
     */
    protected function findUserInDatabase($email, $username, $gid)
    {
        // Determine role based on GID
        $role = $this->mapGidToRole($gid);
        
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
                    'role' => 'student',
                    'email' => $student->mail,
                    'name' => $student->firstname . ' ' . $student->lastname,
                    'model' => $student
                ];
            }
        } elseif ($role === 'teacher') {
            $teacher = Teacher::where('mail', $email)->first();
            
            if ($teacher) {
                return [
                    'id' => $teacher->id,
                    'type' => 'teacher',
                    'role' => 'teacher',
                    'email' => $teacher->mail,
                    'name' => $teacher->firstname . ' ' . $teacher->lastname,
                    'model' => $teacher
                ];
            }
        } elseif ($role === 'supervisor') {
            $supervisor = Supervisor::where('mail', $email)->first();
            
            if ($supervisor) {
                return [
                    'id' => $supervisor->id,
                    'type' => 'supervisor',
                    'role' => 'supervisor',
                    'email' => $supervisor->mail,
                    'name' => $supervisor->firstname . ' ' . $supervisor->lastname,
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
                    'role' => 'department',
                    'name' => $department->name,
                    'model' => $department
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Map GID to user role
     *
     * @param string|null $gid
     * @return string
     */
    protected function mapGidToRole($gid)
    {
        $roles = [
            '5' => 'student',
            '8' => 'teacher',
            '90' => 'supervisor',
            '68' => 'department',
        ];
        
        return $roles[$gid] ?? 'unknown';
    }
    
    /**
     * Track user session activity
     *
     * @param Request $request
     * @param array $user
     * @return void
     */
    protected function trackUserSession(Request $request, $user)
    {
        try {
            // Get existing session record
            $session = \Illuminate\Support\Facades\DB::table('user_sessions')
                ->where('user_id', $user['id'])
                ->where('user_type', $user['type'])
                ->where('ip_address', $request->ip())
                ->where('user_agent', substr($request->userAgent(), 0, 500))
                ->first();
            
            if ($session) {
                // Update existing session
                \Illuminate\Support\Facades\DB::table('user_sessions')
                    ->where('id', $session->id)
                    ->update([
                        'last_active_at' => now(),
                        'updated_at' => now(),
                    ]);
            } else {
                // Create new session record
                \Illuminate\Support\Facades\DB::table('user_sessions')->insert([
                    'user_id' => $user['id'],
                    'user_type' => $user['type'],
                    'ip_address' => $request->ip(),
                    'user_agent' => substr($request->userAgent(), 0, 500),
                    'last_active_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to track user session: ' . $e->getMessage());
            // Don't interrupt the request flow if session tracking fails
        }
    }
}