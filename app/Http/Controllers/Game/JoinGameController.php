<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JoinGameController extends Controller
{
    public function __construct(private GameService $gameService) {}

    public function __invoke(Request $request, Game $game): JsonResponse
    {
        $user = User::create([
            'name' => $request->get('userName')
        ]);
        Auth::login($user);

//        $gameService->joinGame($game, $user);

        $game = $this->gameService->createGame($user, $request->get('expansionIds'));
        $user->load('whiteCards');
        $user->load('blackCards');
    }
}
