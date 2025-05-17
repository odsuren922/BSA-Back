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
    
        // Always use UTC for comparison
        $now = Carbon::now('UTC');
        $this->info('Current UTC time: ' . $now->toDateTimeString());
    
        try {
            $notifications = EmailNotification::where('status', 'scheduled')->get();
    
            $this->info('Total scheduled notifications: ' . $notifications->count());
    
            foreach ($notifications as $notification) {
                // Force Carbon to treat scheduled_at as UTC (even if +08 is saved)
                $scheduledAt = Carbon::parse($notification->scheduled_at)->setTimezone('UTC');
    
                $this->info("ID {$notification->id}: Scheduled for {$scheduledAt->toDateTimeString()} UTC, " . 
                    ($scheduledAt->lte($now) ? 'READY TO SEND' : 'NOT YET DUE'));
            }
    
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