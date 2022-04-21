<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\RoundWinner;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Http\Request;

class StoreRoundWinnerController
{
    public function __construct(public GameService $service)
    {
    }

    public function __invoke(Request $request, Game $game): \Illuminate\Http\JsonResponse
    {
        $user = User::findOrFail($request->get('user_id'));

        $this->service->selectWinner($game, $user);

        return response()->json();
    }
}
