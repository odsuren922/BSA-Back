<?php

namespace App\Console\Commands;

use App\Models\EmailNotification; // Add this import
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send-scheduled';
    protected $description = 'Send scheduled notifications that are due';

    /**
     * Execute the command.
     *
     * @param NotificationService $notificationService
     * @return int
     */
    public function handle(NotificationService $notificationService)
    {
        $this->info('Sending scheduled notifications...');
        
        // Debug: Show current time
        $now = Carbon::now();
        $this->info('Current time: ' . $now->toDateTimeString());
        
        try {
            // Debug: Show notifications that should be sent
            $notifications = EmailNotification::where('status', 'scheduled')
                ->get();
            
            $this->info('Total scheduled notifications: ' . $notifications->count());
            
            foreach ($notifications as $notification) {
                $scheduledAt = Carbon::parse($notification->scheduled_at);
                $this->info("ID {$notification->id}: Scheduled for {$notification->scheduled_at}, " . 
                    ($scheduledAt->lte($now) ? 'READY TO SEND' : 'NOT YET DUE'));
            }
            
            // Proceed with sending
            $count = $notificationService->sendScheduledNotifications();
            
            $this->info("Successfully sent {$count} scheduled notifications.");
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error sending scheduled notifications: ' . $e->getMessage());
            Log::error('Error sending scheduled notifications: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return 1;
        }
    }
}