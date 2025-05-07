<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AssignedGradingResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'student_id'=>$this->student_id,

            'grading_component' => new GradingComponentResource($this->whenLoaded('gradingComponent')),
            'thesis' => new ThesisResource($this->whenLoaded('thesis')),
            'student' => new StudentResource($this->whenLoaded('student')),
            'assigned_by_type' => $this->assigned_by_type,
            'assigned_by_id' => $this->assigned_by_id,
            'thesis_cycle' => new ThesisCycleResource($this->whenLoaded('thesisCycle')),
            'score' => new ScoreResource($this->filtered_score),

            'assigned_by' => $this->assignedBy
                ? [
                    'id' => $this->assignedBy->id,
                    'firstname' => $this->assignedBy->firstname ?? '',
                    'lastname' => $this->assignedBy->lastname ?? '',
                    'dep_id' => $this->assignedBy->dep_id??'',
                    'superior'=> $this->assignedBy->superior??'',
                    'mail'=> $this->assignedBy->mail??'',
                ]
                : null,
        ];
    }
}
