<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailNotificationRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_id',
        'recipient_id',
        'recipient_type',
        'email',
        'status',
        'sent_at',
        'opened_at',
        'status_message'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

    public function notification()
    {
        return $this->belongsTo(EmailNotification::class, 'notification_id');
    }

    public function recipient()
    {
        return $this->morphTo();
    }
}