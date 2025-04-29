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
        $latestThesis = $this->student->thesis->last();

        return [
            'id' => $this->id,
            'status' => $this->status,
            'student' => [
                'id' => $this->student->id,
                'lastname' => $this->student->lastname,
                'firstname' => $this->student->firstname,
                'studentId' => $this->student->id,
                'sisi_id' => $this->student->sisi_id,
                'mail' => $this->student->mail,
                'program' => $this->student->program,
                'thesis' => $latestThesis ? [
                    'id' => $latestThesis->id,
                    'name_mongolian' => $latestThesis->name_mongolian,
                    'name_english' => $latestThesis->name_english,
                    'supervisor' => $latestThesis->supervisor ? [
                        'id' => $latestThesis->supervisor->id,
                        'firstname' => $latestThesis->supervisor->firstname,
                        'lastname' => $latestThesis->supervisor->lastname,
                        'email' => $latestThesis->supervisor->email,
                    ] : null
                ] : null
            ],
            'meta' => [
                'createdAt' => $this->created_at->toIso8601String(),
                'updatedAt' => $this->updated_at->toIso8601String()
            ]
        ];
        
    }
}
