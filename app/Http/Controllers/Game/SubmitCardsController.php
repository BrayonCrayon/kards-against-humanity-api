<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitCardRequest;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SubmitCardsController extends Controller
{
    public function __construct(private GameService $gameService)
    {
        $this->middleware('auth');
    }

    public function __invoke(SubmitCardRequest $request, Game $game): JsonResponse
    {
        $this->gameService->submitCards($request->get('whiteCardIds'), $game);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
