<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\WhiteCardsCollection;
use App\Http\Resources\WhiteCardsResource;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\Request;

class DrawWhiteCardsController extends Controller
{

    private GameService $service;

    public function __construct(GameService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Game $game)
    {
        $cards = $this->service->drawWhiteCards(auth()->user(), $game);

        return response()->json(WhiteCardsResource::collection($cards));
    }
}
