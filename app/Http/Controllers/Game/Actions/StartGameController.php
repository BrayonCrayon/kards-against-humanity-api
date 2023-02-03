<?php

namespace App\Http\Controllers\Game\Actions;

use App\Events\RoundStart;
use App\Http\Controllers\Controller;
use App\Models\Game;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StartGameController extends Controller
{
    public function __invoke(Request $request, Game $game): void
    {
        $this->authorize('start', $game);
        $game->update([
            'selection_ends_at' => Carbon::now()->addSeconds($game->setting->selection_timer)->unix()
        ]);
        event(new RoundStart($game->id, $game->nonJudgeUsers()->pluck('users.id')));
    }
}
