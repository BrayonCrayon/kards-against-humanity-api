<?php

namespace App\Http\Controllers\Game;

use App\Events\CardsSubmitted;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubmitCardRequest;
use App\Models\Game;
use App\Models\WhiteCard;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SubmitCardsController extends Controller
{
    public function __construct(private GameService $gameService)
    {
        $this->middleware('auth');
    }

    public function __invoke(SubmitCardRequest $request, Game $game): JsonResponse
    {
        $this->gameService->submitCards($request->get('whiteCardIds'), $game, auth()->user());

        event(new CardsSubmitted($game, request()->user()));

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
