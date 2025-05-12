<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


class ExternalReviewerScoreResource extends JsonResource
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
            'external_reviewer_id' => $this->external_reviewer_id,
            'student_id' => $this->student_id,
            'grading_component' => $this->gradingComponent->name ?? null,
            'score' => $this->score,

            'created_at' => $this->created_at,
        ];
    }
}
