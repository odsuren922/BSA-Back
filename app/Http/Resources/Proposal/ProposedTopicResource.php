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
            'is_archived' => $this->is_archived,
            'thesis_cycle_id' => $this->thesis_cycle_id,
            'created_by_id' => $this->created_by_id,
            'created_by_type' => $this->created_by_type,
            'thesis_cycle' => $this->whenLoaded('thesisCycle', function () {
                return [
                    'id' => $this->thesisCycle->id,
                    'name' => $this->thesisCycle->name,
                    'year' => $this->thesisCycle->year,
                    'end_year' => $this->thesisCycle->end_year,
                    'semester' => $this->thesisCycle->semester,
                ];
            }),
            'creator' => $this->whenLoaded('createdBy', function () {
                return [
                    'id' => $this->createdBy->id,
                    'firstname' => $this->createdBy->firstname ?? null,
                    'lastname' => $this->createdBy->lastname ?? null,
                ];
            }),

           'statusMn' => $this->translatedStatus(),

            'status' => $this->status,
            'field_values' => ProposalFieldValueResource::collection($this->whenLoaded('fieldValues')),
            'created_at' => $this->created_at,
            'approval_logs' => TopicApprovalLogResource::collection($this->whenLoaded('approvalLogs')),
            'topic_requests' => ProposalTopicRequestResource::collection($this->whenLoaded('topicRequests')),

        ];
    }
    
}
