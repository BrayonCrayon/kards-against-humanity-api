<?php

use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;

test('user relationship brings back user type', function () {
    $gameUser = GameUser::factory()->create();
    expect($gameUser->user)->toBeInstanceOf(User::class);
});

test('game relationship brings back game type', function () {
    $gameUser = GameUser::factory()->create();
    expect($gameUser->game)->toBeInstanceOf(Game::class);
});
