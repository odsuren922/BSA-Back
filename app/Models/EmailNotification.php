<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject', 
        'content', 
        'target_type', 
        'target_criteria', 
        'metadata', 
        'scheduled_at',
        'created_by_id',
        'created_by_type',
        'status'
    ];

    protected $casts = [
        'target_criteria' => 'array',
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function recipients()
    {
        return $this->hasMany(EmailNotificationRecipient::class, 'notification_id');
    }

    public function createdBy()
    {
        return $this->morphTo();
    }
}