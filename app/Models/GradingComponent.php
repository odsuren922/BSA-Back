<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GradingComponent extends Model {
    use HasFactory;
    protected $fillable = ['grading_schema_id', 'score','description', 'by_who', 'name', 'order','scheduled_week'];

    public function translateByWho()
{
    return match ($this->by_who) {
        'supervisor' => 'Удирдагч багш',
        'examiner' => 'Шүүмж багш',
        'committee' => 'Комисс',
        default => 'Тодорхойгүй',
    };
}


    public function gradingSchema() {
        return $this->belongsTo(GradingSchema::class);
    }
    public function gradingCriteria() {
        return $this->hasMany(GradingCriteria::class);
    }

   

    public function scores()
    {
        return $this->hasMany(Score::class, 'component_id');
    }

    public function committeeScores()
    {
        return $this->hasMany(CommitteeScore::class, 'component_id');
    }
}