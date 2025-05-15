<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TrackUserSession
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
        $response = $next($request);
        
        // Only track authenticated requests
        if (Auth::check()) {
            $user = Auth::user();
            
            // Find or create a session record
            $session = DB::table('user_sessions')
                ->where('user_id', $user->id)
                ->where('ip_address', $request->ip())
                ->where('user_agent', substr($request->userAgent(), 0, 500))
                ->first();
            
            if ($session) {
                // Update existing session
                DB::table('user_sessions')
                    ->where('id', $session->id)
                    ->update([
                        'last_active_at' => now(),
                        'updated_at' => now(),
                    ]);
            } else {
                // Create new session
                DB::table('user_sessions')->insert([
                    'user_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'user_agent' => substr($request->userAgent(), 0, 500),
                    'last_active_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        return $response;
    }
}