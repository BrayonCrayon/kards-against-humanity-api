<?php

namespace App\Http\Controllers\Game\Round;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserGameWhiteCardResource;
use App\Models\Game;
use App\Models\GameWinner;
use Illuminate\Http\Request;

class GetRoundWinnerController extends Controller
{
    public function __invoke(Request $request, Game $game)
    {
        $winner = GameWinner::whereGameId($game->id)
            ->whereBlackCardId($game->currentBlackCard->id)
            ->get();

        $whiteCardIds = $winner->pluck('white_card_id');

        $whiteCards = $winner->first()->user->whiteCardsInGame()->whereIn('white_card_id', $whiteCardIds->toArray())->get();

        return response()->json([
            'user_id' => $winner->first()->user->id,
            'submitted_cards' => UserGameWhiteCardResource::collection($whiteCards)
        ]);
    }
}
