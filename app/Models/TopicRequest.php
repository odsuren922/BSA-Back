<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        // 'student_id',
        'requested_by_id',
        'requested_by_type',
        'req_note',
        'is_selected',
        'selected_at',
    ];
    public $incrementing = false;
    protected $keyType = 'string';

    // Disable the automatic timestamps
    public $timestamps = false;

public function student()
{
    return $this->belongsTo(Student::class, 'created_by_id')->where('created_by_type', 'student');
}

public function teacher()
{
    return $this->belongsTo(Teacher::class, 'created_by_id')->where('created_by_type', 'teacher');
}
public function topic() {
    return $this->belongsTo(Topic::class, 'topic_id');
}




}
