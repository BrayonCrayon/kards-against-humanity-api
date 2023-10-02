<?php

use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameExpansion;

test('game relationship brings back game type', function () {
    $gameExpansion = GameExpansion::factory()
        ->for(Expansion::factory())
        ->create();
    expect($gameExpansion->game)->toBeInstanceOf(Game::class);
});

test('expansion relationship brings back expansion type', function () {
    $gameExpansion = GameExpansion::factory()
        ->for(Expansion::factory())
        ->create();
    expect($gameExpansion->expansion)->toBeInstanceOf(Expansion::class);
});
