<?php

namespace App\Models\Proposal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProposalField extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_en',
        'description',
        'dep_id',
        'type',
        'targeted_to',
        'is_required',
        'status',
    ];
    

    public function thesisCycle()
    {
        return $this->belongsTo(ThesisCycle::class);
    }

    public function fieldValues()
    {
        return $this->hasMany(ProposalFieldValue::class, 'field_id');
    }
}