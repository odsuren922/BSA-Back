<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThesisCycleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //return parent::toArray($request);

        return [
            'id' => $this->id,
            'name' => $this->name,  // Example field
            'year' => $this->year,
            'end_year' => $this->end_year,
            'semester' => $this->semester,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,

            'grading_schema' => new GradingSchemaResource($this->whenLoaded('gradingSchema')),
            'theses' => ThesisResource::collection($this->whenLoaded('theses')),
            'deadlines' => ThesisCycleDeadlineResource::collection($this->whenLoaded('deadlines')), // ✅ added
'reminders' => ReminderResource::collection($this->whenLoaded('reminders')),

            //TODO COMMITTEE: INFO
        ];
    }
}
