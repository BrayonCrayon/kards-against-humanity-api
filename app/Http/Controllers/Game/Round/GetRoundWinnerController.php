<?php

namespace App\Http\Controllers\Game\Round;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserGameWhiteCardResource;
use App\Models\BlackCard;
use App\Models\Game;
use App\Models\RoundWinner;
use Illuminate\Http\Request;

class GetRoundWinnerController extends Controller
{
    public function __invoke(Request $request, Game $game, BlackCard $blackCard)
    {
        $this->authorize('get', $game);

        $winner = RoundWinner::whereGameId($game->id)
            ->whereBlackCardId($blackCard->id)
            ->get();

        $whiteCardIds = $winner->pluck('white_card_id');

        $whiteCards = $winner->first()->user->whiteCardsInGame()->whereIn('white_card_id', $whiteCardIds->toArray())->get();

        return response()->json([
            'data' => [
                'user_id' => $winner->first()->user->id,
                'submitted_cards' => UserGameWhiteCardResource::collection($whiteCards)
            ]
        ]);
    }
}
