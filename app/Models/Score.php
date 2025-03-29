<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model {
    use HasFactory;
    protected $fillable = ['student_id', 'teacher_id', 'committee_id', 'grading_component_id', 'score_got'];
    public function gradingComponent() {
        return $this->belongsTo(GradingComponent::class);
    }
}
