<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GameStateResource extends JsonResource
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
            'currentUser' => UserResource::make($this->resource->getPlayer(auth()->user()->id)),
            'blackCard' => BlackCardResource::make($this->resource->black_card),
            'hasSubmittedWhiteCards' => auth()->user()->hasSubmittedWhiteCards,
            'submittedWhiteCardIds' => auth()->user()->submittedWhiteCardIds,
            'hand' => UserGameWhiteCardResource::collection(auth()->user()->hand)
        ];
    }
}
