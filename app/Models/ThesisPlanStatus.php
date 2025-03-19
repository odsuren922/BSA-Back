<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThesisPlanStatus extends Model
{
    use HasFactory;

    protected $table = 'thesis_plan_status';

    protected $fillable = ['thesis_id', 'student_sent', 'teacher_status', 'department_status'];

    public function thesisPlan()
    {
        return $this->belongsTo(Thesis::class, 'thesis_id');
    }
}
