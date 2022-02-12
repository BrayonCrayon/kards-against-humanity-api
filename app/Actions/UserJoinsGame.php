<?php

namespace App\Actions;

use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserJoinsGame
{
    public function __invoke(Game $game, $name)
    {
        if (Auth::check()) {
            return;
        }

        $user = User::create([
            'name' => $name
        ]);
        Auth::login($user);

        $this->gameService->drawWhiteCards($user, $game);
        $this->gameService->joinGame($game, $user);
    }
}
