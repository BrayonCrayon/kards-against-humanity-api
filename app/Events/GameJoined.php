<?php

namespace App\Events;

use App\Models\Game;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Game $game, public User $user) {}

    public function broadcastWith()
    {
        return [
            'gameId' => $this->game->id,
            'userId' => $this->user->id
        ];
    }

    public function broadcastAs() {
        return 'game.joined';
    }

    public function broadcastOn()
    {
        return new Channel('game-' . $this->game->id);
    }
}
