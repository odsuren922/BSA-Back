<?php

// app/Models/CommitteeScore.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommitteeScore extends Model
{
    protected $fillable = [
        'score_id',
        'thesis_id',
        'student_id',
        'committee_member_id',
        'component_id',
        'score',
    ];

    public function score()
    {
        return $this->belongsTo(Score::class);
    }

    public function thesis()
    {
        return $this->belongsTo(Thesis::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function component()
    {
        return $this->belongsTo(GradingComponent::class, 'component_id');
    }

    public function committeeMember()
    {
        return $this->belongsTo(CommitteeMember::class);
    }
}

