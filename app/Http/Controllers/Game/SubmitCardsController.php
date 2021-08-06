<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitCardRequest;
use App\Models\Game;
use App\Models\UserGameWhiteCards;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubmitCardsController extends Controller
{
    public function __invoke(SubmitCardRequest $request, Game $game): JsonResponse
    {
        $user = Auth::user();

        $cardsToSelect = UserGameWhiteCards::where('game_id', $game->id)
            ->where('user_id', $user->id)
            ->whereIn('white_card_id', $request->get('whiteCardIds'))
            ->get();

        $cardsToSelect->each(function ($card) {
           $card->selected = true;
           $card->save();
        });

        return response()->json();
    }
}
