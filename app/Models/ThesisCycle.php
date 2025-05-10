<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThesisCycle extends Model {
    use HasFactory;
    protected $fillable = ['name', 'year', 'end_year','semester', 'start_date', 'end_date', 'grading_schema_id','status','dep_id'];

     public function gradingSchema() {
        return $this->belongsTo(GradingSchema::class);
    }
     public function theses()
    {
    return $this->hasMany(Thesis::class, 'thesis_cycle_id');
    }
    public function deadlines()
    {
        return $this->hasMany(ThesisCycleDeadline::class);
    }
}