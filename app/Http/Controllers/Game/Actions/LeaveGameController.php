<?php

namespace App\Http\Controllers\Game\Actions;

use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveGameController
{
    public function __invoke(Request $request, Game $game, GameService $service): JsonResponse
    {
        if (auth()->user()->id === $game->judge_id) {
            $nextJudge = $service->nextJudge($game);
            $service->updateJudge($game, $nextJudge->id);
        }

        $game->users()->detach(auth()->user()->id);
        auth()->user()->hand()->forceDelete();
        return response()->json();
    }
}
