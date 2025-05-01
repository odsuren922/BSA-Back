<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
//TODO SHORT NAME

class Supervisor extends Model
{
    use HasFactory, HasApiTokens;
    protected $table = 'teachers';
    protected $fillable = ['mail', 'firstname', 'lastname','degree', 'superior', 'dep_id', 'password', 'status'];
    protected $hidden = ['password'];
    public function thesis()
    {
        return $this->hasMany(Thesis::class, 'supervisor_id');
    }

}
