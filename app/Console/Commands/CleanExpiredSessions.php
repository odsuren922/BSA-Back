<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:clean {--days=7 : Number of days of inactivity before a session is considered expired}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean expired user sessions from the database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoff = Carbon::now()->subDays($days);
        
        $this->info("Cleaning sessions inactive since: {$cutoff}");
        
        // Delete expired user sessions
        $userSessionsDeleted = DB::table('user_sessions')
            ->where('last_active_at', '<', $cutoff)
            ->delete();
        
        $this->info("Deleted {$userSessionsDeleted} expired user sessions");
        
        // Delete expired Sanctum tokens
        $tokensDeleted = DB::table('personal_access_tokens')
            ->where('created_at', '<', $cutoff)
            ->where(function($query) {
                $query->where('expires_at', '<', Carbon::now())
                      ->orWhereNull('expires_at');
            })
            ->delete();
        
        $this->info("Deleted {$tokensDeleted} expired personal access tokens");
        
        // Clean Laravel sessions table
        $laravelSessionsDeleted = DB::table('sessions')
            ->where('last_activity', '<', $cutoff->timestamp)
            ->delete();
        
        $this->info("Deleted {$laravelSessionsDeleted} expired Laravel sessions");
        
        // Enforce max sessions per user if configured
        $maxSessions = config('oauth.max_sessions_per_user', 0);
        
        if ($maxSessions > 0) {
            $this->enforceMaxSessionsPerUser($maxSessions);
        }
        
        return 0;
    }
    
    /**
     * Enforce maximum number of sessions per user
     * 
     * @param int $maxSessions
     * @return void
     */
    protected function enforceMaxSessionsPerUser($maxSessions)
    {
        $this->info("Enforcing maximum of {$maxSessions} sessions per user");
        
        // Get users with more than the max allowed sessions
        $usersWithExcessSessions = DB::table('user_sessions')
            ->select('user_id', 'user_type', DB::raw('COUNT(*) as session_count'))
            ->groupBy('user_id', 'user_type')
            ->having('session_count', '>', $maxSessions)
            ->get();
        
        $sessionsRemoved = 0;
        
        foreach ($usersWithExcessSessions as $user) {
            // Get all sessions for this user, ordered by last activity (newest first)
            $userSessions = DB::table('user_sessions')
                ->where('user_id', $user->user_id)
                ->where('user_type', $user->user_type)
                ->orderBy('last_active_at', 'desc')
                ->get();
            
            // Keep the most recent sessions up to the max limit
            $sessionsToRemove = $userSessions->slice($maxSessions);
            
            foreach ($sessionsToRemove as $session) {
                DB::table('user_sessions')
                    ->where('id', $session->id)
                    ->delete();
                
                $sessionsRemoved++;
            }
        }
        
        if ($sessionsRemoved > 0) {
            $this->info("Removed {$sessionsRemoved} excess sessions to enforce max sessions limit");
        } else {
            $this->info("No excess sessions found");
        }
    }
}