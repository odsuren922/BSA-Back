<?php

namespace App\Models\Proposal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicApprovalLog extends Model
{
    protected $fillable = [
        'topic_id',
        'reviewer_id',
        'reviewer_type',
        'action',
        'comment',
        'acted_at',
    ];

    public function topic()
    {
        return $this->belongsTo(ProposedTopic::class);
    }

    public function reviewer()
    {
        return $this->morphTo();
    }
}