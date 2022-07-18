<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Http\Resources\GameStateResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetGameStateController
{
    public function __invoke(Request $request, Game $game): JsonResource
    {
        $game->load('judge', 'users');
        return GameStateResource::make($game);
    }
}
