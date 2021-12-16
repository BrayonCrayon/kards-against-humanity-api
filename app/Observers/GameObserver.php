<?php

namespace App\Observers;

use App\Models\Game;

class GameObserver
{
    /**
     * Handle the Game "created" event.
     *
     * @param  \App\Models\Game  $game
     * @return void
     */
    public function creating(Game $game)
    {
        if (preg_match('/[0-9]{4}/', $game->code)) {
            $game->code = 'D' . substr($game->code, 1);
        }
    }
}
