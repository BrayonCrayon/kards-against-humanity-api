<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'hasSubmittedWhiteCards' => $this->resource->hasSubmittedWhiteCards,
            'score' => $this->resource->score,
            'isSpectator' => $this->whenPivotLoadedAs('gameState', 'game_users', function () {
                return $this->resource->gameState->is_spectator;
            }),
            'redrawCount' => $this->whenPivotLoadedAs('gameState', 'game_users', function () {
                return $this->resource->gameState->redraw_count;
            }),
        ];
    }
}
