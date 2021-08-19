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

    public function getAvailableUsers(Game $game)
    {
        // find a user with a black card
        $userWithBlackCard = $game->getBlackCardUser();

        return $game->users->filter(fn($user) => $user->id !== $userWithBlackCard->id);
    }

    public function __invoke(Request $request, Game $game)
    {
        $users = $game->users;

//        $lastUser = $users->last();
        $firstUser = $users->first();
        $users->push($firstUser);
//        $users->push($lastUser);

        $users->sliding(2)->each(function($pair) use ($game) {
            if ($pair->first()->id === $game->judge_id) {
                $game->update([
                    'judge_id' => $pair->last()->id
                ]);
                return false;
            }
        });

//        $game->userGameBlackCards()->first()->delete();
//
//        $pickedCard = $this->gameService->grabBlackCards($newUser, $game, $game->expansions->pluck('id'));
    }
}
