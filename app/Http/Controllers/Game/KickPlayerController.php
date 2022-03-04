<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use App\Models\UserGameWhiteCards;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KickPlayerController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request, Game $game, User $user): JsonResponse
    {
        $this->authorize('kick', $game);

        GameUser::whereUserId($user->id)->delete();
        UserGameWhiteCards::whereUserId($user->id)->delete();
        $user->delete();

        return response()->json();
    }
}
