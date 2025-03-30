<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'eventType' => $this->event_type,
            'date' => $this->formatted_date,
            'startTime' => $this->start_time_formatted,
            'endTime' => $this->end_time_formatted,
            'location' => $this->location,
            'room' => $this->room,
            'notes' => $this->notes,
            'rawDate' => $this->date->toDateString(),
            'rawStart' => $this->start_time->format('H:i:s'),
            'rawEnd' => $this->end_time?->format('H:i:s'),
            'committee' => $this->whenLoaded('committee', fn() => [
                'id' => $this->committee->id,
                'name' => $this->committee->name
            ]),
            'meta' => [
                'createdAt' => $this->created_at->toIso8601String(),
                'updatedAt' => $this->updated_at->toIso8601String()
            ]
        ];
    }
}
