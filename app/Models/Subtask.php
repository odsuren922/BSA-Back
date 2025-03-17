<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subtask extends Model
{
    use HasFactory;
    
    protected $fillable = ['task_id', 'name', 'start_date', 'end_date', 'description'];

    public function task()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }
}
