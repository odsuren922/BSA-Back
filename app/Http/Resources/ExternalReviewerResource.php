<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ExternalReviewerResource extends JsonResource
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
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'email' => $this->email,
            'phone' => $this->phone,
            'organization' => $this->organization,
            'position' => $this->position,
            'committee_id'=> $this->committee_id,
            'created_at' => $this->created_at->toDateTimeString(),
            'scores' => ExternalReviewerScoreResource::collection($this->whenLoaded('scores')),

            'role' =>'external',
        ];
    }
}
