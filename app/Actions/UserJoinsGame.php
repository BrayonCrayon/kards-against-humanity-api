<?php

namespace App\Actions;

use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;

class UserJoinsGame
{
    public function __construct(private GameService $service)
    {
    }

    public function __invoke(Game $game, $name)
    {
        $user = User::create([
            'name' => $name
        ]);
        Auth::login($user);

        $this->service->drawWhiteCards($user, $game);
        $this->service->joinGame($game, $user);
    }
}
