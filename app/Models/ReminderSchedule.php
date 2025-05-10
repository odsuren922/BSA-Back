<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['scheduleable_type', 'scheduleable_id', 'scheduled_at'];

    public function scheduleable()
    {
        return $this->morphTo();
    }
}
