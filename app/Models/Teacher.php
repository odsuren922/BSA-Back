<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'dep_id', 'firstname', 'lastname', 'degree', 'superior','mail', 'numof_choosed_stud'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function department()
    {
        return $this->belongsTo(Department::class, 'dep_id');
    }

    public function topics()
    {
        return $this->morphMany(Topic::class, 'created_by');
    }
    public function thesis()
    {
        return $this->hasMany(Thesis::class, 'supervisor_id');
    }

    public function committeeMemberships()
{
    return $this->hasMany(CommitteeMember::class);
}

public function committees()
{
    return $this->belongsToMany(Committee::class, 'committee_members')
                ->withPivot('role', 'status', 'is_chairperson', 'assigned_at')
                ->withTimestamps();
}
}
