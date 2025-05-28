<?php

namespace App\Http\Resources\Proposal;

use Illuminate\Http\Resources\Json\JsonResource;

class ProposalFieldResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_en' => $this->name_en,
            'description' => $this->description,
            'type' => $this->type,
            'targeted_to' => $this->targeted_to,
            'is_required' => $this->is_required,
            'value' => $this->value,
            'dep_id' => $this->dep_id,
        ];
    }
}
