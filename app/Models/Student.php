<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'dep_id', 'firstname', 'lastname', 'program', 'mail', 'phone', 'is_choosed', 'proposed_number', 'sisi_id'];
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    public function department()
    {
        return $this->belongsTo(Department::class, 'dep_id');
    }

    public function topics()
    {
        return $this->morphMany(Topic::class, 'created_by');
    }
    public function topicRequestApproved()
    {
        return $this->hasOne(TopicRequest::class)
                    ->where('status', 'approved')
                    ->latest(); // хамгийн сүүлд баталсан
    }
    public function selectedTopicRequest()
    {
        return $this->hasOne(TopicRequest::class, 'requested_by_id')
                    ->where('is_selected', true)
                    ->where('requested_by_type', 'student');
    }

    public function thesis()
    {
        return $this->hasMany(Thesis::class, 'student_id');
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }

    public function committeeScores()
    {
        return $this->hasMany(CommitteeScore::class);
    }

    public function committeeAssignments()
    {
        return $this->hasMany(CommitteeStudent::class);
    }

//     public function thesisScores()
// {
//     return $this->hasMany(ThesisScore::class, 'student_id')
//         ->where('given_by', 'committee');
// }

}