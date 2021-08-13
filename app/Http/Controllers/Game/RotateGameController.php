<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\BlackCard;
use App\Models\Game;
use App\Models\UserGameBlackCards;
use Illuminate\Http\Request;

class RotateGameController extends Controller
{
    public function __invoke(Request $request, Game $game)
    {
        // find a user with a black card
        $userWithBlackCard = $game->getBlackCardUser();

        // delete black card
        $test = $game->userGameBlackCards()->first()->delete();

        // give black card to the next user
        $users = $game->users->filter(fn ($user) => $user->id !== $userWithBlackCard->id);

        // get the used black cards from the game
        $usedBlackCards = $game->userGameBlackCards()->onlyTrashed()->get();

        // select a new black card from the deck that hasn't been used
        $nextBlackCard = BlackCard::whereNotIn('id', $usedBlackCards->pluck('id'))->inRandomOrder()->first();
        // "give" that gard to the next user
//        $users->first()->deal($nextBlackCard);;
        $user = $users->first();
        UserGameBlackCards::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'black_card_id' => $nextBlackCard->id
        ]);

    }
}
