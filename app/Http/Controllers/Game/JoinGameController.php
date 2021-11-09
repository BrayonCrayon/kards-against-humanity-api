<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Http\Requests\JoinGameRequest;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class JoinGameController extends Controller
{
    public function __construct(private GameService $gameService)
    {
    }

    public function __invoke(JoinGameRequest $request, string $gameCode): JsonResponse
    {
        $user = User::create([
            'name' => $request->get('name')
        ]);
        Auth::login($user);

        $game = Game::where('code', $gameCode)->first();

        $this->gameService->drawWhiteCards($user, $game);
        $this->gameService->joinGame($game, $user);

        $user->load('whiteCards');

        return response()->json([
            'user' => $user,
            'game' => $game
        ]);
    }
}
