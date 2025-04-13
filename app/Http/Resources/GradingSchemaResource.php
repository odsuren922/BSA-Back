<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GradingSchemaResource extends JsonResource
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
            'year' => $this->year,
            'name' => $this->name,
            'description' => $this->description,
            'step_num' => $this->step_num,
            
            'grading_components' => GradingComponentResource::collection($this->whenLoaded('gradingComponents')),
        ];
    }
}
