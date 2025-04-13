<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GradingComponentResource extends JsonResource
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
            //TODO:: EDIT ::scheduled_week
            'id' => $this->id,
            'name' => $this->name,
            'score' => $this->score,
            'assessedBy' => $this->by_who,
            'order' => $this->order,
            'by_who' => $this->by_who,
            'scheduled_week' => $this->scheduled_week,
             'schema' => new GradingSchemaResource($this->whenLoaded('gradingSchema')),
            // 'criteria' => GradingCriteriaResource::collection($this->whenLoaded('gradingCriteria')),

    
            'createdAt' => $this->created_at->toDateTimeString(),
            'updatedAt' => $this->updated_at->toDateTimeString(),
        ];
    }
}
