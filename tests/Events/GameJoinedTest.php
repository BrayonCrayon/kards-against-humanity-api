<?php

use App\Events\GameJoined;
use App\Models\Game;
use App\Models\User;

it('returns game and user information from broadcast with', function () {
    $this->user = User::factory()->create();
    $this->game = Game::create([
        'name' => 'Krombopulos Michael',
        'judge_id' => $this->user->id,
        'code' => strval(random_int(0000, 9999))
    ]);
    $this->game->users()->save($this->user);

    $gameJoined = new GameJoined($this->game, $this->user);

    $payload = [
        'gameId' => $this->game->id,
        'userId' => $this->user->id,
    ];

    expect($gameJoined->broadcastWith())->toEqual($payload);
});
