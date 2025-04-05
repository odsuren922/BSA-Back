<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CommitteeStudentResource extends JsonResource
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
            'status' => $this->status,
            //'joinedAt' => $this->joined_at,
            'student' => [
                'id' => $this->student->id,
                'lastname' => $this->student->lastname,
                'firstname' => $this->student->firstname,
                'studentId' => $this->student->id,
                'sisi_id' => $this->student->sisi_id,
                'mail' => $this->student->mail,
                'program' => $this->student->program, 
                'thesis' => $this->student->thesis,
               
                
            ],
            // 'committee' => $this->whenLoaded('committee', fn() => [
            //     'id' => $this->committee->id,
            //     'name' => $this->committee->name
            // ]),
            'meta' => [
                'createdAt' => $this->created_at->toIso8601String(),
                'updatedAt' => $this->updated_at->toIso8601String()
            ]
        ];
    }
}
