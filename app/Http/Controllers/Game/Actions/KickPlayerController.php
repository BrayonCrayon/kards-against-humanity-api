<?php

namespace App\Http\Controllers\Game\Actions;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use App\Models\UserGameWhiteCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KickPlayerController extends Controller
{
    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function __invoke(Request $request, Game $game, User $user): JsonResponse
    {
        $this->authorize('kick', $game);

        GameUser::whereUserId($user->id)->delete();
        UserGameWhiteCard::whereUserId($user->id)->delete();
        $user->delete();

        return response()->json();
    }
}
