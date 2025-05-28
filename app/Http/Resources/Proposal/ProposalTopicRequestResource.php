<?php

namespace App\Http\Resources\Proposal;

use Illuminate\Http\Resources\Json\JsonResource;

class ProposalTopicRequestResource extends JsonResource
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
            'topic_id' => $this->topic_id,
            'req_note' => $this->req_note,
            'is_selected' => $this->is_selected,
            'selected_at' => $this->selected_at,
            'requested_by_id' => $this->requested_by_id,
            'requested_by_type' => $this->requested_by_type,
            'requested_by' => $this->whenLoaded('requestedBy'),
            'topic' => $this->whenLoaded('topic'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
