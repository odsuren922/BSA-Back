<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThesisCycle extends Model {
    use HasFactory;
    protected $fillable = ['name', 'year', 'semester', 'start_date', 'end_date', 'grading_schema_id','status'];
    public function gradingSchema() {
        return $this->belongsTo(GradingSchema::class);
    }
}