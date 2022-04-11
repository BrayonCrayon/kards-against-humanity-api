<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Game;
use Illuminate\Http\Request;

class PlayersController extends Controller
{
    public function __invoke(Request $request, Game $game)
    {
        return response()->json([
            'data' => UserResource::collection($game->users)
        ]);
    }
}
