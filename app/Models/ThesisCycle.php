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
     public function theses()
    {
    return $this->hasMany(Thesis::class, 'thesis_cycle_id');
    }
}