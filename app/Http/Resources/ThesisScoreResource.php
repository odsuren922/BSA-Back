<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThesisScoreResource extends JsonResource
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
            'score' => $this->score,
            'comment' => $this->comment,
            'given_by' => $this->given_by,
            'grading_component_id' => $this->grading_component_id,
            'grading_component' => new GradingComponentResource($this->whenLoaded('gradingComponent')),
            'teacher' => new TeacherResource($this->whenLoaded('teacher')),
            'committee' => new CommitteeResource($this->whenLoaded('committee')),

            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
