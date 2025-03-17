<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'thesis_id',
         'name'
        ];

    public function subprojects()
    {
        return $this->hasMany(Subtasks::class);
    }

    public function thesis()
    {
        return $this->belongsTo(Thesis::class);
    }
}
