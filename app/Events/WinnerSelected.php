<?php

namespace App\Events;

use App\Models\Game;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use JetBrains\PhpStorm\ArrayShape;

class WinnerSelected implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(public Game $game, public User $user) {}

    /**
     * @return array
     */
    #[ArrayShape(['gameId' => "string", 'userId' => "int", 'blackCardId' => "int"])]
    public function broadcastWith(): array
    {
        return [
            'gameId' => $this->game->id,
            'userId' => $this->user->id,
            'blackCardId' => $this->game->blackCard->id
        ];
    }

    /**
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'winner.selected';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn(): Channel|array
    {
        return new Channel("game-{$this->game->id}");
    }
}
