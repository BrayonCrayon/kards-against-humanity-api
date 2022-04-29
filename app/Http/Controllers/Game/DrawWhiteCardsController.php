<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\WhiteCardsResource;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DrawWhiteCardsController extends Controller
{

    private GameService $service;

    public function __construct(GameService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function __invoke(Request $request, Game $game): JsonResponse
    {
        $cards = $this->service->drawWhiteCards(auth()->user(), $game);

        return response()->json(WhiteCardsResource::collection($cards));
    }
}
