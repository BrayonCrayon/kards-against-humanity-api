<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\RoundWinner;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Http\Request;

class StoreRoundWinnerController extends Controller
{
    public function __construct(public GameService $service)
    {
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request, Game $game)
    {
        $user = User::findOrFail($request->get('user_id'));

        $this->service->selectWinner($game, $user);

        return response()->json();
    }
}
