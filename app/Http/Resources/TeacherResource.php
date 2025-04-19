<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
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
        'firstname' => $this->firstname,
        'lastname' => $this->lastname,
        'degree' => $this->degree,
        'superior' => $this->superior,
        'mail' => $this->mail,
        'numof_choosed_stud' => $this->numof_choosed_stud,
        'department' => new DepartmentResource($this->whenLoaded('department')),
        'theses' => ThesisResource::collection($this->whenLoaded('thesis')),
        // 'topics' => TopicResource::collection($this->whenLoaded('topics')),
    ];

    }
}
