<?php

namespace App\Actions;

use App\Models\Game;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;

class UserJoinsGame
{
    public function __construct(private GameService $service)
    {
    }

    public function __invoke(Game $game, string $name)
    {
        $creatingUser = new CreatingUser();

        if (Auth::check()) {
            Auth::logout();
        }

        $user = $creatingUser($name);

        $this->service->drawWhiteCards($user, $game);
        $this->service->joinGame($game, $user);
    }
}
