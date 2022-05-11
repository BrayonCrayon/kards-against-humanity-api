<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserGameWhiteCardResource;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RedrawController extends Controller
{

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function __invoke(Request $request, Game $game, GameService $gameService): AnonymousResourceCollection
    {

        $this->authorize('redraw', $game);
        auth()->user()->hand()->delete();
        $gameService->drawWhiteCards(auth()->user(), $game);
        $gameService->incrementDrawCount($game, auth()->user());

        return UserGameWhiteCardResource::collection(auth()->user()->hand);
    }
}
