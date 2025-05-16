<?php
// app/Http/Middleware/RequireTokenMiddleware.php (updated with better error handling)

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\TokenService;

class RequireTokenMiddleware
{
    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function handle(Request $request, Closure $next)
    {
        try {
            // Check multiple token sources
            $token = $this->tokenService->getTokenFromRequest($request);
            
            if (!$token) {
                Log::warning('No authentication token found for API request', [
                    'path' => $request->path(),
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                
                return $this->createErrorResponse('Authentication required');
            }
            
            // Set token for downstream middleware
            $request->headers->set('Authorization', 'Bearer ' . $token);
            
            // Continue with the request
            return $next($request);
        } catch (\Exception $e) {
            Log::error('Error in RequireTokenMiddleware: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);
            
            return $this->createErrorResponse('Authentication error: ' . $this->getSafeErrorMessage($e));
        }
    }
    
    /**
     * Create a standardized error response
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createErrorResponse($message, $statusCode = 401)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => 'auth_error',
            'timestamp' => now()->toIso8601String(),
        ], $statusCode);
    }
    
    /**
     * Get a sanitized error message without sensitive information
     *
     * @param \Exception $e
     * @return string
     */
    protected function getSafeErrorMessage(\Exception $e)
    {
        $message = $e->getMessage();
        
        // Remove tokens, keys, secrets from error message
        $message = preg_replace('/Bearer\s+[a-zA-Z0-9\._\-]+/', 'Bearer [REDACTED]', $message);
        $message = preg_replace('/client_secret=[^&]+/', 'client_secret=[REDACTED]', $message);
        $message = preg_replace('/password=[^&]+/', 'password=[REDACTED]', $message);
        
        // Provide a generic message for production
        if (app()->environment('production')) {
            return 'Authentication failed';
        }
        
        return $message;
    }
}