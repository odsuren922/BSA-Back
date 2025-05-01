<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;


class Student extends Model
{
    use HasFactory,HasApiTokens;
    
    protected $table = 'students';
    
    protected $fillable = ['firstname', 'lastname','program','dep_id', 'mail', 'password', 'phonenumber'];

    protected $hidden = ['password'];
    public function thesis()
    {
        return $this->hasMany(Thesis::class, 'student_id');
    }
}
