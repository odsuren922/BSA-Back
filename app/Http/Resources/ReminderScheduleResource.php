<?php

namespace App\Http\Resources;
use Illuminate\Http\Request;

use Illuminate\Http\Resources\Json\JsonResource;

class ReminderScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'scheduled_at' => $this->scheduled_at,
            'scheduleable_type' => $this->scheduleable_type,
            'scheduleable_id' => $this->scheduleable_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
