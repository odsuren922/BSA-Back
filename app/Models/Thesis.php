<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Thesis extends Model
{
    use HasFactory;

    protected $table = 'thesis';
    protected $fillable = ['supervisor_id', 'student_id', 'status', 'submitted_to_teacher_at', 'submitted_to_dep_at', 'name_mongolian', 'name_english','description', 'thesis_cycle_id'];
    protected $casts = [
        'topic' => 'array', // Automatically converts JSON to an array
    ];
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function supervisor()
    {
        return $this->belongsTo(Teacher::class, 'supervisor_id');
    }
    public function projects()
    {
        return $this->hxasMany(Project::class);
    }
    public function status()
    {
        return $this->hasOne(ThesisPlanStatus::class, 'thesis_id');
    }
    public function thesisCycle() {
        return $this->belongsTo(ThesisCycle::class);
    }

}