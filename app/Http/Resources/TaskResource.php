<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'name' => $this->name,
            'thesis_id' => $this->thesis_id,
            
            'subtasks' => SubtaskResource::collection($this->whenLoaded('subtasks')),
            'thesis' => new ThesisResource($this->whenLoaded('thesis')),  // assuming ThesisResource exists
        ];
    }
}
