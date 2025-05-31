<?php

namespace App\Models\Proposal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ThesisCycle;

class ProposedTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'created_by_id',
        'created_by_type',
        'thesis_cycle_id',
       'topic_content_id',
        'status',
        'is_archived',
    ];

    public function translatedStatus()
    {
        $statuses = [
            'draft' => 'Ноорог',
            'submitted' => 'Илгээсэн',
            'rejected' => 'Татгалзсан',
            'approved' => 'Зөвшөөрөгдсөн',
            'chosen' => 'Сонгогдсон',
            'archived' => 'Архивлагдсан',
        ];
    
        return $statuses[$this->status] ?? $this->status;
    }
    


    public function fieldValues()
    {
        return $this->hasMany(ProposalFieldValue::class, 'proposed_topic_id');
    }
    public function topicContent()
    {
        return $this->belongsTo(TopicContent::class, 'topic_content_id');
    }
    
    
    public function thesisCycle()
    {
        return $this->belongsTo(ThesisCycle::class);
    }

    public function approvalLogs()
    {
        return $this->hasMany(TopicApprovalLog::class, 'topic_id');
    }
    public function topicRequests()
{
    return $this->hasMany(ProposalTopicRequest::class, 'topic_id');
}

    
    public function createdBy(){
        return $this->morphTo();
    }

    


}