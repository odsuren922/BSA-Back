<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommitteeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     * Laravel-ийн JSON API resource бөгөөд Committee модель дээр үндэслэн 
     * frontend-д ойлгомжтой бүтэцтэй JSON хариу бэлддэг.
     */
    public function toArray($request)
    {
        //return parent::toArray($request);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'status' => $this->translatedStatus(), // Translated status
            'thesis_cycle' => new ThesisCycleResource($this->whenLoaded('thesis_cycle')), //
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'grading_component' => new GradingComponentResource($this->whenLoaded('gradingComponent')),
            'members' => CommitteeMemberResource::collection($this->whenLoaded('members')),
            'students' => CommitteeStudentResource::collection($this->whenLoaded('students')),
            'schedules' => ScheduleResource::collection($this->whenLoaded('schedules')),
            'scores' => ScoreResource::collection($this->whenLoaded('scores')),
            // 'scores' => CommitteeScoreResource::collection($this->whenLoaded('scores')),no cuz member is connected score
            
        ];
    }
}
