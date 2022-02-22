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

    public function __invoke(Game $game, $name)
    {
        $creatingUser = new CreatingUser();
        $user = Auth::check() ? Auth::user() : $creatingUser($name);

        $this->service->drawWhiteCards($user, $game);
        $this->service->joinGame($game, $user);
    }
}
