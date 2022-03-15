<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use App\Models\UserGameWhiteCards;
use App\Services\GameService;
use Illuminate\Http\Request;

class RedrawController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Game $game, GameService $gameService)
    {
        auth()->user()->whiteCardsInGame()->delete();
        $gameService->drawWhiteCards(auth()->user(), $game);
    }
}
