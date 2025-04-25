<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteeStudent extends Model
{
    use HasFactory;
    protected $fillable = ['committee_id', 'student_id','thesis_id', 'status', 'joined_at'];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    public function thesis()
    {
        return $this->belongsTo(Thesis::class, 'thesis_id');
    }
    public function scores()
    {
        return $this->hasMany(Score::class);
    }
    
    // public function scores()
    // {
    //     return $this->hasMany(ThesisScore::class, 'student_id', 'student_id')
    //         ->where('given_by', 'committee');
    // }
}
