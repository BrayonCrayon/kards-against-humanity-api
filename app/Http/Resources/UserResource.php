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
            'has_submitted_white_cards' => $this->resource->hasSubmittedWhiteCards,
            'score' => $this->resource->score,
            'redrawCount' => $this->whenPivotLoadedAs('gameState', 'game_users', function () {
                return $this->resource->gameState->redraw_count;
            }),
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
