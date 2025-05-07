<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
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
        
        try {
            $count = $notificationService->sendScheduledNotifications();
            
            $this->info("Successfully sent {$count} scheduled notifications.");
            Log::info("Successfully sent {$count} scheduled notifications.");
            
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