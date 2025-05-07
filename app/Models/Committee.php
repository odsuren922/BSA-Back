<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'grading_component_id', 'dep_id','thesis_cycle_id', 'status', 'color'];


    public function translatedStatus()
    {
        $statuses = [
            'planned' => 'Төлөвлөгдсөн',
            'active' => 'Идэвхтэй',
            'done' => 'Дууссан',
            'cancelled' => 'Цуцлагдсан',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function gradingComponent()
    {
        return $this->belongsTo(GradingComponent::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'dep_id');
    }

    public function members()
    {
        return $this->hasMany(CommitteeMember::class);
    }

    public function students()
    {
        return $this->hasMany(CommitteeStudent::class);
    }
    public function thesis_cycle()
    {
        return $this->belongsTo(ThesisCycle::class);
    }
    public function schedules()
    {
        return $this->hasMany(Schedule::class, 'committee_id');
    }

    public function scores()
    {
        return $this->hasMany(Score::class, 'given_by_id')
            ->where('given_by_type', 'App\Models\Committee');
    }
    
    // public function scores()
    // {
    //     return $this->hasMany(CommitteeScore::class, 'committee_id');
    // }



}
