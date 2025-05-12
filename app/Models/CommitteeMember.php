<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    use HasFactory;
    protected $fillable = ['committee_id', 'teacher_id', 'role', 'status', 'assigned_at'];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
    public function committeeScores()
    {
        return $this->hasMany(CommitteeScore::class);
    }
}
