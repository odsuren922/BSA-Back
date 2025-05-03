<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'thesis' => new ThesisResource($this->whenLoaded('thesis')),
            'grading_component_id' => $this->component_id,
            'component' => new GradingComponentResource($this->whenLoaded('component')),
            'score' => $this->score,
            'given_by_type' => $this->given_by_type,
            'given_by_id' => $this->given_by_id,
            'committee_student_id' => $this->committee_student_id,
            'committee_scores' => CommitteeScoreResource::collection($this->whenLoaded('committeeScores')),
        ];
    }
}
