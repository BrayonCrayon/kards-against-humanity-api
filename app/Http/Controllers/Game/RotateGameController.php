<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;

class RotateGameController extends Controller
{
    public function __invoke(Request $request, Game $game)
    {
        // get game users
        $users = $game->users();

        // find a user with a black card
        // delete black card
        // give black card to the next user
    }
}
