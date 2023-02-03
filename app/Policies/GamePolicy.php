<?php

namespace App\Policies;

use App\Models\Game;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GamePolicy
{
    use HandlesAuthorization;

    public function get(User $user, Game $game)
    {
        return $user->games()->firstOrFail()->id === $game->id;
    }

    public function kick(User $user, Game $game)
    {
        return $user->id === $game->judge_id;
    }

    public function start(User $user, Game $game)
    {
        return $user->id === $game->judge_id;
    }

    public function redraw(User $user, Game $game)
    {
        $match = $game->getPlayer($user->id);
        return $match->gameState->redraw_count < $game->redraw_limit;
    }

    public function update(User $user, Game $game)
    {
        return $game->users->contains($user);
    }
}
