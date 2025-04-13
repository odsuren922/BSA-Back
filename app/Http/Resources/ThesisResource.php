<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThesisResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        //TODO:: ADD GRADING MODEL AND SCORE 
        return [
            'id' => $this->id,
            'name_mongolian' => $this->name_mongolian,
            'name_english' => $this->name_english,
            'description' => $this->description,

            'supervisor' => new TeacherResource($this->whenLoaded('supervisor')),
            'student' => new StudentResource($this->whenLoaded('student')),
            'thesis_cycle' => new ThesisCycleResource($this->whenLoaded('thesisCycle')),
            'thesisPlanStatus' => new ThesisPlanStatusResource($this->whenLoaded('thesisPlanStatus')),
            'scores' => ThesisScoreResource::collection($this->whenLoaded('scores')),

            'submitted_to_teacher_at' => $this->submitted_to_teacher_at,
            'submitted_to_dep_at' => $this->submitted_to_dep_at,
            'status' => $this->status,
        ];
    }
}
