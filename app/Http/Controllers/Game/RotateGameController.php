<?php

namespace App\Http\Controllers\Game;

use App\Events\GameRotation;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\Request;

class RotateGameController extends Controller
{
    public function __construct(public GameService $gameService)
    {
        $this->middleware('auth');
    }

    public function __invoke(Request $request, Game $game)
    {
        $userIds = $game->users()->pluck('users.id');

        $currentJudgeIndex = $userIds->search($game->judge_id);
        $nextJudgeIndex = ($currentJudgeIndex + 1) % $userIds->count();

        $this->gameService->updateJudge($game, $userIds[$nextJudgeIndex]);
        $this->gameService->discardWhiteCards($game);
        $this->gameService->discardBlackCard($game);
        $this->gameService->drawBlackCard($game);
        // TODO: Do this and emit event for every user
        $this->gameService->drawWhiteCards($game->users->first(), $game);

        // for each user, draw white cards, and then emit event
        event(new GameRotation($game, $game->users->first()->whiteCards->toArray()));
    }
}
