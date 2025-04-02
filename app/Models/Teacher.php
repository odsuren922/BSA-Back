<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Teacher extends Model
{
    use HasApiTokens, HasFactory;
    protected $table = 'teachers';
    protected $fillable = ['id', 'dep_id', 'firstname', 'lastname', 'degree', 'superior', 'mail', 'numof_choosed_stud'];
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

}
