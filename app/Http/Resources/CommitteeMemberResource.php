<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommitteeMemberResource extends JsonResource
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
            'role' => $this->role,
            'status' => $this->status,
            'isChairperson' => $this->is_chairperson,

            'teacher' => [
                'id' => $this->teacher->id,
                'firstname' => $this->teacher->firstname,
                'lastname' => $this->teacher->lastname,
                'email' => $this->teacher->mail,
            ],
            'committee' => $this->whenLoaded(
                'committee',
                fn() => [
                    'id' => $this->committee->id,
                    'name' => $this->committee->name,
                ],
            ),
            'committee_memberships' => CommitteeMemberResource::collection($this->whenLoaded('committeeMemberships')),

            'meta' => [
                'createdAt' => $this->created_at->toIso8601String(),
                'updatedAt' => $this->updated_at->toIso8601String(),
            ],
            'committeeScores' => CommitteeScoreResource::collection($this->whenLoaded('committeeScores')),
        ];
    }
}
