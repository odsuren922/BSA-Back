<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'teachers' => TeacherResource::collection($this->whenLoaded('teachers')),
            'supervisors' => SupervisorResource::collection($this->whenLoaded('supervisors')),
            'students' => StudentResource::collection($this->whenLoaded('students')),
            'head_of_department' => new TeacherResource($this->whenLoaded('headOfDepartment')),

            // 'proposalForms' => ProposalFormResource::collection($this->whenLoaded('proposalForms')),
            // 'createdAt' => $this->created_at->toDateTimeString(),
            // 'updatedAt' => $this->updated_at->toDateTimeString(),
        

        ];
    }
}
