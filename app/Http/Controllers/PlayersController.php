<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayersController extends Controller
{
    public function __invoke(Request $request, Game $game): JsonResponse
    {
        $this->authorize('get', $game);
        return response()->json([
            'data' => UserResource::collection($game->users)
        ]);
    }
}
