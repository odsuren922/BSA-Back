<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Score extends Model
{
    protected $fillable = [
        'thesis_id',
        'student_id',
        'component_id',
        'score',
        'given_by_type',
        'given_by_id',
        'committee_student_id',
    ];

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

    public function committeeStudent()
    {
        return $this->belongsTo(CommitteeStudent::class);
    }
//Dep, Admin, Committee, Outside person
    public function givenBy(): MorphTo
    {
        return $this->morphTo();
    }

    public function committeeScores()
    {
        return $this->hasMany(CommitteeScore::class);
    }
}
