<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ThesisPlanStatusResource extends JsonResource
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
            'thesis_id' => $this->thesis_id,
            'student_sent' => $this->student_sent,
            'student_sent_at' => $this->student_sent_at,

            'teacher_status' => $this->teacher_status,
            'teacher_status_updated_at' => $this->teacher_status_updated_at,

            'department_status' => $this->department_status,
            'department_status_updated_at' => $this->department_status_updated_at,
        ];
    }
}
