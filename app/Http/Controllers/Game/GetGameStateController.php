<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\GameResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GetGameStateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param Game $game
     * @return JsonResource
     */
    public function __invoke(Request $request, Game $game): JsonResource
    {
        $game->load('judge', 'users');
        return GameResource::make($game);
    }
}
