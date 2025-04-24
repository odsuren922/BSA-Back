<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
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
            'program' => $this->program,
            'mail' => $this->mail,
            'phone' => $this->phone,
            'is_choosed' => $this->is_choosed,
            'proposed_number' => $this->proposed_number,
            'sisi_id' => $this->sisi_id,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'theses' => ThesisResource::collection($this->whenLoaded('thesis')),
            //  'topics' => TopicResource::collection($this->whenLoaded('topics')),
        ];

    }
}
