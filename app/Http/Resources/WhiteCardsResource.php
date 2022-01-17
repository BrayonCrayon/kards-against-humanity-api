<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WhiteCardsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
          'id' => $this->resource->id,
          'text' => $this->resource->text,
          'order' => $this->resource->order,
          'expansion_id' => $this->resource->expansion_id,
        ];
    }
}
