<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteeStudent extends Model
{
    use HasFactory;
    protected $fillable = ['committee_id', 'student_id', 'status', 'joined_at'];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
