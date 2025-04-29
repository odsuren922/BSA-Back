<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'committee_id',
        'event_type',
        'start_datetime',
        'end_datetime',
        'location',
        'room',
        'notes'
    ];
    
    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    // public function getFormattedDateAttribute()
    // {
    //     return $this->date->format('Y-m-d');
    // }

    // public function getStartTimeFormattedAttribute()
    // {
    //     return $this->start_time->format('H:i');
    // }

    // public function getEndTimeFormattedAttribute()
    // {
    //     return $this->end_time ? $this->end_time->format('H:i') : null;
    // }

}
