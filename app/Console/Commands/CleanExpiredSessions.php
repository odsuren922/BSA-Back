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
            ->where('expires_at', '<', Carbon::now())
            ->delete();
        
        $this->info("Deleted {$tokensDeleted} expired personal access tokens");
        
        // Clean Laravel sessions table
        $laravelSessionsDeleted = DB::table('sessions')
            ->where('last_activity', '<', $cutoff->timestamp)
            ->delete();
        
        $this->info("Deleted {$laravelSessionsDeleted} expired Laravel sessions");
        
        return 0;
    }
}