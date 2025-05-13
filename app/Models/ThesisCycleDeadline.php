<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThesisCycleDeadline extends Model
{
    use HasFactory;

    protected $fillable = [
        'thesis_cycle_id',
        'type',
        'related_id',
        'related_type',
        'title',
        'description',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
    ];

    public function related()
{
    return $this->morphTo();
}


    public function cycle()
    {
        return $this->belongsTo(ThesisCycle::class, 'thesis_cycle_id');
    }

    public function relatedComponent()
    {
        return $this->belongsTo(GradingComponent::class, 'related_id');
    }

}
