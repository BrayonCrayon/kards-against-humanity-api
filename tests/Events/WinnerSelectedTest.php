<?php

use App\Events\WinnerSelected;
use App\Models\Game;
use App\Models\User;

beforeEach(function () {
    $this->game = Game::factory()->create();
    $this->user = User::factory()->create();

    $this->event = new WinnerSelected($this->game, $this->user);
});

it('sends user id and game id in event payload', function () {
    $payload = [
        'gameId' => $this->game->id,
        'userId' => $this->user->id,
        'blackCardId' => $this->game->blackCard->id
    ];

    expect($this->event->broadcastWith())->toEqual($payload);
});

it('broadcasts on correct channel name', function () {
    expect($this->event->broadcastOn()->name)->toEqual("game-{$this->game->id}");
});

it('broadcasts on correct event name', function () {
    expect($this->event->broadcastAs())->toEqual("winner.selected");
});
