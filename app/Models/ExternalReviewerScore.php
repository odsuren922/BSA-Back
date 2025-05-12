<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalReviewerScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_reviewer_id',
        'student_id',
        'grading_component_id',
        'score',
   
    ];

    public function externalReviewer()
    {
        return $this->belongsTo(ExternalReviewer::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function gradingComponent()
    {
        return $this->belongsTo(GradingComponent::class);
    }
}