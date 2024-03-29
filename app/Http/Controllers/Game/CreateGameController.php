<?php

namespace App\Http\Controllers\Game;

use App\Http\Requests\Game\CreateGameRequest;
use App\Http\Resources\GameStateResource;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CreateGameController
{
    public function __construct(private GameService $gameService)
    {
    }

    public function __invoke(CreateGameRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->get('name')
        ]);
        Auth::login($user);
        $game = $this->gameService->createGame($user, $request->get('expansionIds'), $request->settings());

        $user->load('whiteCards');

        return response()->json(['data' => GameStateResource::make($game)]);
    }
}
