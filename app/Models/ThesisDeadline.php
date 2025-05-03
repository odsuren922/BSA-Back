<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ThesisDeadline extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    protected $fillable = [
        'department_id',
        'thesis_cycle_id',
        'target_people', 
        'name',          
        'description',
        'grading_component_id',
            //     'program_id',
             //     'created_by',
        'deadline_date',
        'reminder_days',
    ];


    protected $casts = [
        'deadline_date' => 'datetime',
        'reminder_days' => 'array',
    ];

    /**
     * Get the department that this deadline belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function thesisCycle()
{
    return $this->belongsTo(ThesisCycle::class);
}

public function gradingComponent()
{
    return $this->belongsTo(GradingComponent::class);
}
// 


    /**
     * Calculate the next reminder date for this deadline.
     *
     * @return Carbon|null
     */
    public function getNextReminderDate()
    {
        $now = Carbon::now();
        $deadlineDate = Carbon::parse($this->deadline_date);
        
        if ($deadlineDate->isPast()) {
            return null;
        }

        $reminderDays = collect($this->reminder_days)->sort()->values();
        
        foreach ($reminderDays as $days) {
            $reminderDate = $deadlineDate->copy()->subDays($days);
            if ($reminderDate->isFuture() || $reminderDate->isToday()) {
                return $reminderDate;
            }
        }

        return null;
    }

    /**
     * Get the students who should receive this deadline notification.
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTargetPeople()
{
    switch ($this->target_people) {
        case 'student':
            return $this->getTargetStudents();
        case 'teacher':
            return $this->getTargetTeacher();
        // case 'committee':
        //     return $this->getTargetCommitteeMembers();
        // case 'assistant':
        //     return $this->getTargetAssistants();
        default:
            return collect(); // empty collection
    }
}


    public function getTargetStudents()
    {
        $query = Student::query();
        
        if ($this->department_id) {
            $query->where('dep_id', $this->department_id);
        }
       
        return $query->get();
    }

    public function getTargetSupervisors()
{
    $query = Teacher::query();

    if ($this->department_id) {
        $query->where('department_id', $this->department_id);
    }
      
    return $query->get();


}

public function getTargetCommitteeMembers()
{
    return Teacher::whereHas('committeeMembers', function ($q) {
        $q->whereHas('committee', function ($q2) {
            $q2->where('thesis_cycle_id', $this->thesis_cycle_id);
        });
    })->get();
}


}