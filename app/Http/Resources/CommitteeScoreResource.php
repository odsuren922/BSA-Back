<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommitteeScoreResource extends JsonResource
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
            'score_id' => $this->score_id,
            'thesis' => new ThesisResource($this->whenLoaded('thesis')),
            'student' => new StudentResource($this->whenLoaded('student')),
            'committee_member' => new CommitteeMemberResource($this->whenLoaded('committeeMember')),
            'component' => new GradingComponentResource($this->whenLoaded('component')),
            'score' => $this->score,
            'created_at' => $this->created_at,
        ];
    }
}
