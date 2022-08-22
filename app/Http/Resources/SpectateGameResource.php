<?php

namespace App\Http\Resources;

use App\Models\Game;
use Illuminate\Http\Resources\Json\JsonResource;

class SpectateGameResource extends JsonResource
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
            'game' => GameResource::make($this->resource),
            'users' => UserResource::collection($this->resource->users),
            'user' => UserResource::make($this->resource->spectators()->where('users.id', auth()->user()->id)->first()),
            'blackCard' => BlackCardResource::make($this->resource->blackCard),
        ];
    }
}
