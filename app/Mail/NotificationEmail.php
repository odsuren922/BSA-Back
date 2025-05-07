<?php

namespace App\Mail;

use App\Models\EmailNotification;
use App\Models\EmailNotificationRecipient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NotificationEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $notification;
    public $recipient;

    /**
     * Create a new message instance.
     *
     * @param EmailNotification $notification
     * @param EmailNotificationRecipient $recipient
     * @return void
     */
    public function __construct(EmailNotification $notification, EmailNotificationRecipient $recipient)
    {
        $this->notification = $notification;
        $this->recipient = $recipient;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->notification->subject)
            ->view('emails.notification')
            ->with([
                'content' => $this->notification->content,
                'metadata' => $this->notification->metadata,
                'recipientId' => $this->recipient->id
            ]);
    }
}