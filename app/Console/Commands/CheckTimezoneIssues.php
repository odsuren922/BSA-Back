<?php

namespace App\Console\Commands;

use App\Models\EmailNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckTimezoneIssues extends Command
{
    protected $signature = 'notifications:check-timezone';
    protected $description = 'Check for timezone issues in scheduled notifications';

    public function handle()
    {
        $this->info('Checking timezone configuration and notifications...');
        $this->info('Current server time: ' . Carbon::now()->toDateTimeString());
        $this->info('App timezone: ' . config('app.timezone'));
        $this->info('Current time in UTC: ' . Carbon::now()->setTimezone('UTC')->toDateTimeString());
        
        // Get all scheduled notifications
        $notifications = EmailNotification::where('status', 'scheduled')->get();
        
        $this->info('Found ' . $notifications->count() . ' scheduled notifications');
        
        foreach ($notifications as $notification) {
            $this->info('--------------------------------------');
            $this->info("Notification #{$notification->id}: {$notification->subject}");
            
            // Get the scheduled time as stored in database
            $scheduledRaw = $notification->getRawOriginal('scheduled_at'); // Get raw value
            $scheduledAt = Carbon::parse($notification->scheduled_at);
            
            $this->info("Raw scheduled_at from database: {$scheduledRaw}");
            $this->info("Parsed scheduled_at: {$scheduledAt->toDateTimeString()}");
            $this->info("Scheduled time in UTC: {$scheduledAt->setTimezone('UTC')->toDateTimeString()}");
            
            // Determine if should be sent
            $now = Carbon::now();
            if ($scheduledAt->lte($now)) {
                $this->info('Status: SHOULD BE SENT (scheduled time is in the past)');
                $this->info('Time difference: ' . $scheduledAt->diffForHumans($now));
            } else {
                $this->info('Status: WAITING (scheduled time is in the future)');
                $this->info('Time until sending: ' . $scheduledAt->diffForHumans($now));
            }
        }
        
        return 0;
    }
}