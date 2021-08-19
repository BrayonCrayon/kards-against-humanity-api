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
        $users = $game->users;

        $firstUser = $users->first();
        $users->push($firstUser);

        $users->sliding(2)->each(function($pair) use ($game) {
            if ($pair->first()->id === $game->judge_id) {
                $game->update([
                    'judge_id' => $pair->last()->id
                ]);
                return false;
            }
        });

        $newBlackCard = BlackCard::query()
            ->inRandomOrder()
            ->whereNotIn('id', $game->gameBlackCards->pluck('black_card_id'))
            ->first();

        GameBlackCards::create([
            'game_id' => $game->id,
            'black_card_id' => $newBlackCard->id
        ]);
    }
}
