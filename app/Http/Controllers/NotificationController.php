<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\EmailNotification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class NotificationController extends Controller
{
    protected $notificationService;
    
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    


    /**
     * Store a new notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        if ($request->has('scheduled_at')) {
            // Parse the ISO string from frontend (always in UTC)
            $utcScheduledTime = Carbon::parse($request->input('scheduled_at'));
            
            // Store the time in the database with timezone awareness
            // Laravel will handle conversion based on app timezone
            $data['scheduled_at'] = $utcScheduledTime;
            
            Log::debug('Timezone conversion for scheduled notification', [
                'received_utc_iso' => $request->input('scheduled_at'),
                'app_timezone' => config('app.timezone'),
                'stored_time' => $utcScheduledTime->toDateTimeString()
            ]);
        }

        Log::debug('Notification store request received', [
            'ip' => $request->ip(),
            'method' => $request->method(),
            'has_authorization' => $request->hasHeader('Authorization') ? 'yes' : 'no',
            'auth_header' => $request->hasHeader('Authorization') ? substr($request->header('Authorization'), 0, 15) . '...' : null,
            'content_type' => $request->header('Content-Type'),
            'post_data_keys' => array_keys($request->all()),
        ]);
        
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'target_type' => 'required|string|in:student,teacher,department,specific,thesis_cycle',
            'target_criteria' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after_or_equal:now',
            'metadata' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            Log::warning('Notification validation failed', [
                'errors' => $validator->errors()->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Get authenticated user
        $userInfo = session('oauth_user') ?? null;
        
        if (!$userInfo) {
            Log::warning('No authenticated user info available in session', [
                'session_id' => session()->getId(),
                'all_session_keys' => array_keys(session()->all()),
            ]);
            
            // Try to get user from token
            try {
                $token = $this->extractTokenFromRequest($request);
                
                if ($token) {
                    $oauthService = app(\App\Services\OAuthService::class);
                    $userData = $oauthService->getUserData($token);
                    
                    if ($userData) {
                        Log::info('User data retrieved from token', [
                            'user_id' => $userData['user_id'] ?? 'unknown',
                            'data_fields' => array_keys($userData),
                        ]);
                        
                        // Use token-retrieved user data
                        $userInfo = [
                            'id' => $userData['user_id'] ?? 'system',
                            'role' => $userData['role'] ?? 'system',
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error('Error retrieving user data from token: ' . $e->getMessage());
            }
            
            // If still no user info, use system defaults
            if (!$userInfo) {
                Log::info('Using system user as fallback');
                $userInfo = [
                    'id' => 'system',
                    'role' => 'system',
                ];
            }
        }
        
        // Prepare data for notification creation
        $data = $request->all();
        $data['created_by_id'] = $userInfo['id'] ?? 'system';
        $data['created_by_type'] = $userInfo['role'] ?? 'system';
        
        try {
            $notification = $this->notificationService->createNotification($data);
            
            Log::info('Notification created successfully', [
                'notification_id' => $notification->id,
                'subject' => $notification->subject,
                'recipients_count' => $notification->recipients()->count(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully',
                'data' => [
                    'id' => $notification->id,
                    'subject' => $notification->subject,
                    'status' => $notification->status,
                    'recipients_count' => $notification->recipients()->count()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create notification: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Extract token from request
     *
     * @param Request $request
     * @return string|null
     */
    protected function extractTokenFromRequest(Request $request)
    {
        // Try bearer token
        $token = $request->bearerToken();
        if ($token) {
            return $token;
        }
        
        // Try Authorization header format "Token <token>"
        $header = $request->header('Authorization');
        if ($header && preg_match('/^Token\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        // Try from request parameters
        if ($request->has('access_token')) {
            return $request->input('access_token');
        }
        
        return null;
    }


    
    /**
     * Get a list of all notifications
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $query = EmailNotification::query();
        
        // Apply filters if provided
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        
        if ($request->has('created_by_type')) {
            $query->where('created_by_type', $request->input('created_by_type'));
        }
        
        if ($request->has('created_by_id')) {
            $query->where('created_by_id', $request->input('created_by_id'));
        }
        
        // Order by latest created
        $query->orderBy('created_at', 'desc');
        
        $notifications = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }
    
    /**
     * Get a specific notification with recipients
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $notification = EmailNotification::with('recipients')
            ->findOrFail($id);
            
        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }
    
    /**
     * Manually send a pending notification
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function send($id)
    {
        $notification = EmailNotification::findOrFail($id);
        
        if (!in_array($notification->status, ['pending', 'scheduled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Notification cannot be sent. Current status: ' . $notification->status
            ], 400);
        }
        
        try {
            $this->notificationService->sendNotification($notification);
            
            return response()->json([
                'success' => true,
                'message' => 'Notification sent successfully',
                'data' => [
                    'id' => $notification->id,
                    'status' => $notification->status,
                    'sent_at' => $notification->sent_at
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cancel a scheduled notification
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id)
    {
        $notification = EmailNotification::findOrFail($id);
        
        if ($notification->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'message' => 'Only scheduled notifications can be cancelled. Current status: ' . $notification->status
            ], 400);
        }
        
        $notification->status = 'cancelled';
        $notification->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification cancelled successfully',
            'data' => [
                'id' => $notification->id,
                'status' => $notification->status
            ]
        ]);
    }

    /**
     * Track email opens via tracking pixel
     *
     * @param int $recipientId
     * @return \Illuminate\Http\Response
     */
    public function track($recipientId)
    {
        try {
            $recipient = EmailNotificationRecipient::find($recipientId);
            
            if ($recipient) {
                $recipient->opened_at = now();
                
                // Only update if not already marked as opened
                if ($recipient->status === 'sent') {
                    $recipient->status = 'opened';
                }
                
                $recipient->save();
            }
        } catch (\Exception $e) {
            // Log but don't affect response
            Log::error('Failed to track notification open: ' . $e->getMessage());
        }
        
        // Return a 1x1 transparent pixel GIF
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        
        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}