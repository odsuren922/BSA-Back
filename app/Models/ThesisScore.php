<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThesisScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'thesis_id',
        'grading_component_id',
        'teacher_id',
        'score',
        'comment',
        'given_by',
        'committee_id'
    ];
//TODO::     "error": "Call to undefined relationship [gradingModel] on model [App\\Models\\ThesisScore]."

    public function thesis()
    {
        return $this->belongsTo(Thesis::class);
    }

    public function gradingComponent()
    {
        return $this->belongsTo(GradingComponent::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
    public function committee()
{
    return $this->belongsTo(Committee::class);
}

}
