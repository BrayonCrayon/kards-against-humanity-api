<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class RoundStart implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $gameId, public Collection $userIds)
    {
    }

    public function broadcastWith() : array
    {
        return [
            'gameId' => $this->gameId
        ];
    }

    public function broadcastOn() : array
    {
        return $this->userIds->map(function(int $id) {
            return new Channel("user-$id");
        })->toArray();
    }

    public function broadcastAs() : string
    {
        return 'game.started';
    }
}
