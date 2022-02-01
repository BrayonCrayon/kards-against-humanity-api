<?php

namespace App\Http\Controllers\Game\Round;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameWinner;
use Illuminate\Http\Request;

class GetRoundWinnerController extends Controller
{
    public function __invoke(Request $request, Game $game)
    {
        $winner = GameWinner::whereGameId($game->id)
            ->whereBlackCardId($game->currentBlackCard->id)
            ->with('whiteCard')->get();

        // pluck white_card_ids from winner collection

        $whiteCardIds = $winner->pluck('white_card_id');

        // query user whiteCardInGame with plucked white_card_ids to get selected
        $winner->first()->user->whiteCardsInGame()->whereIn('white_card_id', $whiteCardIds->toArray())->get();

        return response()->json([
            'user_id' => $winner->first()->user->id,
            'submitted_cards' => $winner->map(function ($round_winner) use ($winner) {
                return [
                    'id' => $round_winner->whiteCard->id,
                    'text' => $round_winner->whiteCard->text,
                    'expansion_id' => $round_winner->whiteCard->expansion_id,
                    'order'=> $winner->first()->user->whiteCardsInGame()->whereSelected(true)->whereWhiteCardId($round_winner->whiteCard->id)->firstOrFail()->order,
                ];
            })
//
        ]);
    }
}
