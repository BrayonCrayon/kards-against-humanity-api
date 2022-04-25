<?php

namespace App\Http\Controllers\Game\Spectator;

use App\Http\Resources\SpectateGameResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetSpectateDataController
{
    public function __invoke(Request $request, Game $game): JsonResponse
    {
        return SpectateGameResource::make($game)->toResponse($request);
    }
}
