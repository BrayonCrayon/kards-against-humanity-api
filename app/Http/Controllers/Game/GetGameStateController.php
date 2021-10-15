<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetGameStateController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request, Game $game): JsonResponse
    {
        $game->load('judge', 'users', 'users.whiteCards');
        $game->append('currentBlackCard');
        return response()->json([
            'game' => $game,
            'users' => $game->users
        ]);
    }
}
