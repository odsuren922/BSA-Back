<?php

namespace App\Console\Commands;

use App\Models\EmailNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FixScheduledNotifications extends Command
{
    protected $signature = 'notifications:fix-scheduled';
    protected $description = 'Fix timezone issues in scheduled notifications';

    public function handle()
    {
        $this->info('Looking for scheduled notifications to fix...');
        
        // Get all scheduled notifications
        $notifications = EmailNotification::where('status', 'scheduled')->get();
        
        $this->info('Found ' . $notifications->count() . ' scheduled notifications');
        
        foreach ($notifications as $notification) {
            $this->info('--------------------------------------');
            $this->info("Examining notification #{$notification->id}: {$notification->subject}");
            
            // Get the scheduled time as stored in database
            $scheduledAt = Carbon::parse($notification->scheduled_at);
            
            $this->info("Current scheduled_at: {$scheduledAt->toDateTimeString()}");
            
            // For testing, optionally set this notification to be due now
            if ($this->confirm("Do you want to reschedule this notification to be sent now?")) {
                $newTime = Carbon::now()->subMinutes(5); // 5 minutes in the past
                $notification->scheduled_at = $newTime;
                $notification->save();
                
                $this->info("Notification #{$notification->id} rescheduled to: {$newTime->toDateTimeString()}");
                $this->info("This notification should be picked up by the next scheduler run.");
            }
        }
        
        $this->info('--------------------------------------');
        $this->info('Would you like to create a test notification scheduled for the past?');
        
        if ($this->confirm('Create test notification scheduled for 5 minutes ago?')) {
            $testEmail = $this->ask('Enter test email address:');
            
            $notification = new EmailNotification();
            $notification->subject = 'Test Timezone Fix';
            $notification->content = '<p>This is a test notification scheduled for 5 minutes ago.</p>';
            $notification->target_type = 'specific';
            $notification->target_criteria = ['emails' => [$testEmail]];
            $notification->scheduled_at = Carbon::now()->addMinutes(5);
            $notification->created_by_id = 'system';
            $notification->created_by_type = 'system';
            $notification->status = 'scheduled';
            $notification->save();
            
            $this->info("Test notification #{$notification->id} created and scheduled for: {$notification->scheduled_at->toDateTimeString()}");
            $this->info("Run 'php artisan notifications:send-scheduled' to process it.");
        }
        
        return 0;
    }
}