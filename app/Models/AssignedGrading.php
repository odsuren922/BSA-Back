<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AssignedGrading extends Model
{
    use HasFactory;

    protected $fillable = [
        'grading_component_id',
        'thesis_cycle_id',
        // 'teacher_id',
        // 'assigned_by',
        'assigned_by_type',   
        'assigned_by_id',
        'student_id',
        'thesis_id',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    // public function teacher()
    // {
    //     return $this->belongsTo(Teacher::class, 'teacher_id');
    // }
    public function assignedBy(): MorphTo
{
    return $this->morphTo();
}


    public function gradingComponent()
    {
        return $this->belongsTo(GradingComponent::class, 'grading_component_id');
    }
    public function thesis(){
        return $this->belongsTo(Thesis::class, 'thesis_id');
    }





}
