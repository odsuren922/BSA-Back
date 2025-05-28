<?php

namespace App\Http\Resources\Proposal;

use Illuminate\Http\Resources\Json\JsonResource;

class ProposedTopicResource extends JsonResource
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
            'title_en' => $this->topicContent->title_en ?? null,
            'title_mn' => $this->topicContent->title_mn ?? null,
            'description' => $this->topicContent->description ?? null,
    
            'thesis_cycle_id' => $this->thesis_cycle_id,
            'created_by_id' => $this->created_by_id,
            'created_by_type' => $this->created_by_type,
    
            'creator' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'firstname' => $this->createdBy->firstname ?? null,
                    'lastname' => $this->createdBy->lastname ?? null,
                ];
            }),
            'approval_logs' => TopicApprovalLogResource::collection($this->whenLoaded('approvalLogs')),

           'statusMn' => $this->translatedStatus(),

            'status' => $this->status,
            'field_values' => ProposalFieldValueResource::collection($this->whenLoaded('fieldValues')),
            'created_at' => $this->created_at,
        ];
    }
    
}
