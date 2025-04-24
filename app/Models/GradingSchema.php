<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingSchema extends Model {
    use HasFactory;
    protected $fillable = ['year', 'description', 'step_num', 'name','dep_id'];
    
    public function thesisCycles() {
        return $this->hasMany(ThesisCycle::class);
    }
    public function gradingComponents() {
        return $this->hasMany(GradingComponent::class)->orderBy('scheduled_week');
    }
    
    
}