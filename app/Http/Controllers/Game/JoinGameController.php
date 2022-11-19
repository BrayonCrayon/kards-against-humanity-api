<?php

namespace App\Http\Controllers\Game;

use App\Actions\UserJoinsGame;
use App\Http\Requests\JoinGameRequest;
use App\Http\Resources\GameStateResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;

class JoinGameController
{
    public function __invoke(JoinGameRequest $request, Game $game, UserJoinsGame $userJoinsGame): JsonResponse
    {
        $userJoinsGame($game, $request->input('name'));

        return response()->json([
            'data' => GameStateResource::make($game)
        ]);
    }
}
