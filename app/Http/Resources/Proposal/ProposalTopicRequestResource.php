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
        $alreadySelectedTopic = false;

        if ($this->requested_by_type === 'App\\Models\\Student') {
            $alreadySelectedTopic = \App\Models\Proposal\ProposalTopicRequest::where('requested_by_id', $this->requested_by_id)
                ->where('requested_by_type', 'App\\Models\\Student')
                ->whereHas('topic', fn($q) => $q->where('status', 'chosen'))
                ->exists();
        }
        return [
            'id' => $this->id,
            'topic_id' => $this->topic_id,
            'req_note' => $this->req_note,
            'is_selected' => $this->is_selected,
            'selected_at' => $this->selected_at,
            'requested_by_id' => $this->requested_by_id,
            'requested_by_type' => $this->requested_by_type,
            'already_selected_topic' => $alreadySelectedTopic,
            'thesis_cycle_id' => $this->thesis_cycle_id,
              // THESIS CYCKE NAME YEAR ND END_YEAR AND SEMESTER
              'thesis_cycle' => $this->whenLoaded('thesisCycle', function () {
                return [
                    'id' => $this->thesisCycle->id,
                    'name' => $this->thesisCycle->name,
                    'year' => $this->thesisCycle->year,
                    'end_year' => $this->thesisCycle->end_year,
                    'semester' => $this->thesisCycle->semester,
                ];
            }),
            'requested_by' => [
                'id' => optional($this->requestedBy)->id,
                'firstname' => optional($this->requestedBy)->firstname,
                'lastname' => optional($this->requestedBy)->lastname,
                'program' => optional($this->requestedBy)->program ?? null,
            ],

            'topic' => $this->whenLoaded('topic'),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
