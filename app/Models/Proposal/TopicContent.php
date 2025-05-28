<?php

namespace App\Models\Proposal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title_en',
        'title_mn',
        'description',
    ];

    public function proposedTopics()
    {
        return $this->hasMany(ProposedTopic::class, 'topic_content_id');
    }
}
