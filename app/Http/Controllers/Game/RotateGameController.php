<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\BlackCard;
use App\Models\Game;
use App\Models\UserGameBlackCards;
use App\Services\GameService;
use Illuminate\Http\Request;

class RotateGameController extends Controller
{
    public function __construct(public GameService $gameService)
    {
    }

    public function getAvailableUsers(Game $game)
    {
        // find a user with a black card
        $userWithBlackCard = $game->getBlackCardUser();

        return $game->users->filter(fn($user) => $user->id !== $userWithBlackCard->id);
    }

    public function __invoke(Request $request, Game $game)
    {
        $users = $this->getAvailableUsers($game);

        $game->userGameBlackCards()->first()->delete();

        $newUser = $users->first();

        $pickedCard = $this->gameService->grabBlackCards($newUser, $game, $game->expansions->pluck('id'));

        $game->update([
            'judge_id' => $newUser->id
        ]);
    }
}
