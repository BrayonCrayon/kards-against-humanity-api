<?php

namespace App\Actions;

use App\Models\Game;
use App\Models\GameUser;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;

class UserJoinsGame
{
    public function __construct(
        private readonly GameService $service,
        private readonly CreatingUser $creatingUser
    ) {}

    public function __invoke(Game $game, string $name) : void
    {

        if (GameUser::query()->whereUserId(Auth::id())->whereGameId($game->id)->first()) {
            return;
        }

        if (GameUser::query()->whereUserId(Auth::id())->count() > 0) {
            Auth::logout();
        }

        $user = Auth::check() ? auth()->user() : ($this->creatingUser)($name);
        $this->service->drawWhiteCards($user, $game);
        $this->service->joinGame($game, $user);
    }
}
