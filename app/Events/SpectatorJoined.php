<?php

namespace App\Events;

use App\Models\Game;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SpectatorJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Game $game) {}

    public function broadcastWith()
    {
        return [
            'gameId' => $this->game->id
        ];
    }

    public function broadcastAs() {
        return 'game.spectator.joined';
    }

    public function broadcastOn()
    {
        return new Channel('game-' . $this->game->id);
    }
}
