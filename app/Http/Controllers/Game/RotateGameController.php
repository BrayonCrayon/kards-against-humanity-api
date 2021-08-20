<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\BlackCard;
use App\Models\Game;
use App\Models\GameBlackCards;
use App\Services\GameService;
use Illuminate\Http\Request;

class RotateGameController extends Controller
{
    public function __construct(public GameService $gameService)
    {
    }

    public function __invoke(Request $request, Game $game)
    {
        $userIds = $game->users()->pluck('users.id');

        $currentJudgeIndex = $userIds->search($game->judge_id);
        $nextJudgeIndex = ($currentJudgeIndex + 1) % $userIds->count();

        $this->gameService->updateJudge($game, $userIds[$nextJudgeIndex]);

        $this->gameService->discardBlackCard($game);
        $this->gameService->drawBlackCard($game);
    }
}
