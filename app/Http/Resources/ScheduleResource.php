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
            'start_datetime' => $this->start_datetime,
            'end_datetime' => $this->end_datetime,
            'location' => $this->location,
            'room' => $this->room,
            'notes' => $this->notes,

            'committee' => $this->whenLoaded('committee', function () {
                return [
                    'id' => $this->committee->id ?? null,
                    'name' => $this->committee->name ?? null,
                    'grading_components_name' => $this->committee->gradingComponent->name ?? null,
                    // 'grading_components' => $this->committee->gradingComponent->( {
                    //     return [
                    //         'id' => $component->id,
                    //         'name' => $component->name,
                    //         'weight' => $component->weight,
                    //     ];
                    // }),
                ];
            }),


            // 'meta' => [
            //     'createdAt' => $this->created_at->toIso8601String(),
            //     'updatedAt' => $this->updated_at->toIso8601String(),
            // ],
        ];
    }
}
