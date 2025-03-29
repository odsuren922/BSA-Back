<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingCriteria extends Model {
    use HasFactory;
    protected $table = 'grading_criteria';
    protected $fillable = ['grading_component_id', 'name', 'score'];

    public function gradingComponent() {
        return $this->belongsTo(GradingComponent::class);
    }
}
