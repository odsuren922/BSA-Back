<?php

namespace App\Services;

use App\Models\EmailNotification;
use App\Models\EmailNotificationRecipient;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Department;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\NotificationEmail;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Create a new notification
     *
     * @param array $data
     * @return EmailNotification
     */
    public function createNotification(array $data)
    {
        // Create notification record
        $notification = EmailNotification::create([
            'subject' => $data['subject'],
            'content' => $data['content'],
            'target_type' => $data['target_type'],
            'target_criteria' => $data['target_criteria'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'scheduled_at' => isset($data['scheduled_at']) ? Carbon::parse($data['scheduled_at']) : null,
            'created_by_id' => $data['created_by_id'],
            'created_by_type' => $data['created_by_type'],
            'status' => isset($data['scheduled_at']) ? 'scheduled' : 'pending'
        ]);

        // Resolve recipients
        $recipients = $this->resolveRecipients($notification);
        
        // Store recipient records
        foreach ($recipients as $recipient) {
            EmailNotificationRecipient::create([
                'notification_id' => $notification->id,
                'recipient_id' => $recipient['id'],
                'recipient_type' => $recipient['type'],
                'email' => $recipient['email'],
                'status' => 'pending'
            ]);
        }

        // If not scheduled, send immediately
        if (!isset($data['scheduled_at'])) {
            $this->sendNotification($notification);
        }

        return $notification;
    }

    /**
     * Resolve recipients based on target criteria
     *
     * @param EmailNotification $notification
     * @return array
     */
    protected function resolveRecipients(EmailNotification $notification)
    {
        $recipients = [];
        $targetType = $notification->target_type;
        $criteria = $notification->target_criteria;

        switch ($targetType) {
            case 'student':
                $query = Student::query();
                
                // Apply filters based on criteria
                if (isset($criteria['program'])) {
                    $query->where('program', $criteria['program']);
                }
                
                if (isset($criteria['dep_id'])) {
                    $query->where('dep_id', $criteria['dep_id']);
                }
                
                // Map students to recipients format
                $students = $query->get();
                foreach ($students as $student) {
                    if (!empty($student->mail)) {
                        $recipients[] = [
                            'id' => $student->id,
                            'type' => 'student',
                            'email' => $student->mail
                        ];
                    }
                }
                break;
                
            case 'teacher':
                $query = Teacher::query();
                
                if (isset($criteria['dep_id'])) {
                    $query->where('dep_id', $criteria['dep_id']);
                }
                
                $teachers = $query->get();
                foreach ($teachers as $teacher) {
                    if (!empty($teacher->mail)) {
                        $recipients[] = [
                            'id' => $teacher->id,
                            'type' => 'teacher',
                            'email' => $teacher->mail
                        ];
                    }
                }
                break;
                
            case 'department':
                if (isset($criteria['dep_id'])) {
                    $departments = Department::whereIn('id', (array)$criteria['dep_id'])->get();
                } else {
                    $departments = Department::all();
                }
                
                foreach ($departments as $department) {
                    // Assuming each department has a head with email
                    $head = $department->headOfDepartment;
                    if ($head && !empty($head->mail)) {
                        $recipients[] = [
                            'id' => $department->id,
                            'type' => 'department',
                            'email' => $head->mail
                        ];
                    }
                }
                break;
                
            case 'specific':
                // Handle direct email specification
                if (isset($criteria['emails']) && is_array($criteria['emails'])) {
                    foreach ($criteria['emails'] as $email) {
                        // For specific emails, use email as ID 
                        $recipients[] = [
                            'id' => md5($email),
                            'type' => 'specific',
                            'email' => $email
                        ];
                    }
                }
                break;
                
            case 'thesis_cycle':
                if (isset($criteria['thesis_cycle_id'])) {
                    // Get all theses for this cycle
                    $theses = \App\Models\Thesis::where('thesis_cycle_id', $criteria['thesis_cycle_id'])
                        ->with(['student', 'supervisor'])
                        ->get();
                    
                    // Add students
                    foreach ($theses as $thesis) {
                        if ($thesis->student && !empty($thesis->student->mail)) {
                            $recipients[] = [
                                'id' => $thesis->student->id,
                                'type' => 'student',
                                'email' => $thesis->student->mail
                            ];
                        }
                        
                        // Add supervisors (teachers)
                        if ($thesis->supervisor && !empty($thesis->supervisor->mail)) {
                            $recipients[] = [
                                'id' => $thesis->supervisor->id,
                                'type' => 'teacher',
                                'email' => $thesis->supervisor->mail
                            ];
                        }
                    }
                }
                break;
        }

        return $recipients;
    }

    /**
     * Send a notification
     *
     * @param EmailNotification $notification
     * @return bool
     */
    public function sendNotification(EmailNotification $notification)
    {
        $notification->status = 'processing';
        $notification->save();
        
        $success = true;
        $failCount = 0;
        
        foreach ($notification->recipients as $recipient) {
            if ($recipient->status !== 'pending') {
                continue;
            }
            
            try {
                Mail::to($recipient->email)
                    ->send(new NotificationEmail($notification, $recipient));
                
                $recipient->status = 'sent';
                $recipient->sent_at = now();
                $recipient->save();
            } catch (\Exception $e) {
                $failCount++;
                $success = false;
                
                $recipient->status = 'failed';
                $recipient->status_message = $e->getMessage();
                $recipient->save();
                
                Log::error('Failed to send notification email', [
                    'notification_id' => $notification->id,
                    'recipient_id' => $recipient->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Update notification status
        $notification->sent_at = now();
        $notification->status = $failCount > 0 ? ($failCount == count($notification->recipients) ? 'failed' : 'partial') : 'sent';
        $notification->save();
        
        return $success;
    }

    /**
     * Send scheduled notifications
     *
     * @return int Number of notifications sent
     */
    public function sendScheduledNotifications()
    {
        $now = Carbon::now();
        $count = 0;

        // Get all notifications scheduled to be sent now or in the past
        $notifications = EmailNotification::where('status', 'scheduled')
            ->where('scheduled_at', '<=', $now)
            ->get();

        foreach ($notifications as $notification) {
            if ($this->sendNotification($notification)) {
                $count++;
            }
        }

        return $count;
    }
}