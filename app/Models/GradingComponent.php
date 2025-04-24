<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingComponent extends Model {
    use HasFactory;
    protected $fillable = ['grading_schema_id', 'score', 'by_who', 'name', 'order','scheduled_week'];
    public function gradingSchema() {
        return $this->belongsTo(GradingSchema::class);
    }
    public function gradingCriteria() {
        return $this->hasMany(GradingCriteria::class);
    }
}