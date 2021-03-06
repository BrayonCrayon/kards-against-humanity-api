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
            'redrawLimit' => $this->resource->redraw_limit,
            'judge' => UserResource::make($this->resource->judge),
            'users' => UserResource::collection($this->resource->users),
            'currentUser' => UserResource::make($this->resource->getPlayer(auth()->user()->id)),
            'blackCard' => BlackCardResource::make($this->resource->blackCard),
            'hasSubmittedWhiteCards' => auth()->user()->hasSubmittedWhiteCards,
            'submittedWhiteCardIds' => auth()->user()->submittedWhiteCardIds,
            'hand' => UserGameWhiteCardResource::collection(auth()->user()->hand),
        ];
    }
}
