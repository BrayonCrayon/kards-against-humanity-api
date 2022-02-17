<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserGameWhiteCardResource;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubmittedCardsController extends Controller
{
    public function __construct(private GameService $gameService)
    {
        $this->middleware('auth');
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, Game $game) : JsonResponse
    {
        $data = $this->gameService->getSubmittedCards($game);

        return response()->json([
            'data' => $data
        ]);
    }
}
