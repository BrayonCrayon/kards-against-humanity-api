<?php

namespace App\Http\Controllers\Game\Actions;

use App\Events\CardsSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\SelectCardRequest;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SelectCardsController extends Controller
{
    public function __construct(private GameService $gameService)
    {
        $this->middleware('auth');
    }

    public function __invoke(SelectCardRequest $request, Game $game): JsonResponse
    {
        $this->gameService->selectCards($request->get('whiteCardIds'), $game, auth()->user());

        event(new CardsSubmitted($game, request()->user()));

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
