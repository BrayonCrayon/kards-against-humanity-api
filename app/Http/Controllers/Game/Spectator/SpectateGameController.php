<?php

namespace App\Http\Controllers\Game\Spectator;

use App\Events\SpectatorJoined;
use App\Http\Controllers\Controller;
use App\Http\Resources\SpectateGameResource;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpectateGameController extends Controller
{
    public function __invoke(Request $request, Game $game): SpectateGameResource
    {
        $user = User::create(['name' => 'Spectator X']);
        GameUser::create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'is_spectator' => true
        ]);
        Auth::login($user);
        event(new SpectatorJoined($game));

        return SpectateGameResource::make($game);
    }
}
