<?php

namespace App\Actions;

use App\Models\Game;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;

class UserJoinsGame
{
    public function __construct(private GameService $service, private CreatingUser $creatingUser)
    {
    }

    public function __invoke(Game $game, string $name)
    {
        if (Auth::check()) {
            Auth::logout();
        }

        $user = ($this->creatingUser)($name);

        $this->service->drawWhiteCards($user, $game);
        $this->service->joinGame($game, $user);
    }
}
