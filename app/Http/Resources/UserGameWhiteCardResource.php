<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserGameWhiteCardResource extends JsonResource
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
            'id' => $this->resource->white_card_id,
            'text' => $this->resource->whiteCard->text,
            'expansionId' => $this->resource->whiteCard->expansion_id,
            'order' => $this->resource->order,
            'selected' => $this->resource->selected
        ];
    }
}
