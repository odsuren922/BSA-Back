<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteeMember extends Model
{
    use HasFactory;
    protected $fillable = ['committee_id', 'teacher_id', 'role', 'status', 'is_chairperson', 'assigned_at'];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}
