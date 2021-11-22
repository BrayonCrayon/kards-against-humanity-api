<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
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
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'code' => $this->resource->code,
            'judge' => UserResource::make($this->resource->judge),
            'users' => UserResource::collection($this->resource->users),
            'current_user' => UserResource::make(auth()->user()),
            'current_black_card' => BlackCardResource::make($this->resource->current_black_card),
            'hand' => WhiteCardsResource::collection(auth()->user()->whiteCards),
        ];
    }
}
