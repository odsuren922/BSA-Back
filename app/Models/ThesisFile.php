<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThesisFile extends Model
{
    protected $fillable = ['thesis_id', 'file_path', 'original_name', 'type', 'uploaded_by'];

    public function thesis()
    {
        return $this->belongsTo(Thesis::class);
    }
}

