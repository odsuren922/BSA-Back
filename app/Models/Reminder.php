<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'thesis_cycle_id',
        'component_id',
        'title',
        'description',
        'target_type',
    ];
    


    /**
     * Relationships
     */

    public function thesisCycle()
    {
        return $this->belongsTo(ThesisCycle::class);
    }

    public function component()
    {
        return $this->belongsTo(GradingComponent::class, 'component_id');
    }
    public function schedules()
    {
        return $this->morphMany(ReminderSchedule::class, 'scheduleable');
    }
}
