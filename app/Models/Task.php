<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = ['thesis_id', 'name'];

    public function subtasks()
    {
        return $this->hasMany(Subtask::class, 'task_id')->orderBy('created_at', 'asc');;
    }

    public function thesis()
    {
        return $this->belongsTo(Thesis::class);
    }
}
