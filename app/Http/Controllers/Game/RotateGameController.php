<?php

namespace App\Http\Controllers\Game;

use App\Events\GameRotation;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Http\Request;

class RotateGameController extends Controller
{
    public function __construct(public GameService $gameService)
    {
        $this->middleware('auth');
    }

    public function __invoke(Request $request, Game $game)
    {
        $this->gameService->rotateGame($game);
    }
}
