<?php

namespace App\Http\Resources\Proposal;

use Illuminate\Http\Resources\Json\JsonResource;

class TopicApprovalLogResource extends JsonResource
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
            'action' => $this->action,
            'comment' => $this->comment,
            'acted_at' => $this->acted_at,
            'reviewer' => [
                'id' => $this->reviewer->id,
                'firstname' => $this->reviewer->firstname ,
                'lastname' => $this->reviewer->lastname,
                'type' => class_basename($this->reviewer_type),
            ]
        ];
    }
}
