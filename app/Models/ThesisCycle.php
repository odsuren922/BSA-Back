<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThesisCycle extends Model {
    use HasFactory;
    protected $fillable = ['name', 'year', 'end_year','semester', 'start_date', 'end_date', 'grading_schema_id','status','dep_id'];

     public function gradingSchema() {
        return $this->belongsTo(GradingSchema::class);
    }
     public function theses()
    {
    return $this->hasMany(Thesis::class, 'thesis_cycle_id');
    }
    public function deadlines()
    {
        return $this->hasMany(ThesisCycleDeadline::class);
    }
    public function reminders()
{
    return $this->hasMany(Reminder::class);
}

    public function proposalFields()
    {
        return $this->hasMany(ProposalField::class, 'thesis_cycle_id');
    }

    public function topicRequests()
    {
        return $this->hasMany(ProposalTopicRequest::class, 'thesis_cycle_id');
    }

    public function proposedTopics()
    {
        return $this->hasMany(ProposedTopic::class, 'thesis_cycle_id');
    }
}