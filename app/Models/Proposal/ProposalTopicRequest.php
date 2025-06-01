<?php

namespace App\Models\Proposal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ThesisCycle;
class ProposalTopicRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'thesis_cycle_id',
        'requested_by_id',
        'requested_by_type',
        'req_note',
        // 'is_selected',
        'selected_at',
    ];

    protected $casts = [
        'is_selected' => 'boolean',
        'selected_at' => 'datetime',
    ];

    public function topic()
    {
        return $this->belongsTo(ProposedTopic::class, 'topic_id');
    }

    public function requestedBy()
    {
        return $this->morphTo(null, 'requested_by_type', 'requested_by_id');
    }
    public function thesisCycle()
    {
        return $this->belongsTo(ThesisCycle::class);
    }
}
