<?php

namespace App\Observers;

use App\Events\RoundStart;
use App\Models\Game;

class GameObserver
{
    public function updated(Game $game): void
    {
        if ($game->selection_ends_at && $game->wasChanged('selection_ends_at')) {
            event(new RoundStart($game->id, $game->nonJudgeUsers()->pluck('users.id')));
        }
    }
}
