<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReminderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'thesis_cycle_id' => $this->thesis_cycle_id,
            'component_id' => $this->component_id,
            'title' => $this->title,
            'description' => $this->description,
            'target_type' => $this->target_type,

            // Relationships
            'component' => new GradingComponentResource($this->whenLoaded('component')),
            'schedules' => ReminderScheduleResource::collection($this->whenLoaded('schedules')),
            'thesis_cycle' => new ThesisCycleResource($this->whenLoaded('thesisCycle')),
        ];
    }
}
