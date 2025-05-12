<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ExternalReviewer extends Model
{
    protected $fillable = [
        'firstname',
        'lastname',
        'committee_id',
        'email',
        'phone',
        'organization',
        'position',
    ];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function scores()
    {
        return $this->hasMany(ExternalReviewerScore::class);
    }

}

