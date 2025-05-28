<?php

namespace App\Models\Proposal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ProposalFieldValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'proposed_topic_id',
        'field_id',
        'value',
    ];

    public function topic()
    {
        return $this->belongsTo(ProposedTopic::class, 'proposed_topic_id');
    }

    public function field()
    {
        return $this->belongsTo(ProposalField::class, 'field_id');
    }
    public function proposedTopic()
{
    return $this->belongsTo(ProposedTopic::class, 'proposed_topic_id');
}
}