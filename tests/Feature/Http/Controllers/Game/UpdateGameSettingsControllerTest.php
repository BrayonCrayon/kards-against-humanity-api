<?php

use App\Models\User;

uses(\Tests\Traits\GameUtilities::class);

it('will update game settings', function () {
    $game = $this->createGame();
    $user = $game->users()->first();

    $payload = [
        'selection_timer' => $this->faker->numberBetween(60, 300),
        'has_animations' => true,
    ];

    $this
        ->actingAs($user)
        ->postJson(route('api.game.settings.update', $game), $payload)
        ->assertOk();

    expect($game->fresh()->setting->only(['selection_timer', 'has_animations']))
        ->toEqual($payload);
});

it('will only users in the game to update game settings', function () {
    $game = $this->createGame();
    $guest = User::factory()->create();

    $originalSettings = $game->setting->toArray();

    $payload = [
        'selection_timer' => $this->faker->randomNumber(3) + $game->setting->selection_timer,
    ];

    $this
        ->actingAs($guest)
        ->postJson(route('api.game.settings.update', $game), $payload)
        ->assertForbidden();

    $game->refresh();

    expect($game->setting->selection_timer)->toEqual($originalSettings['selection_timer']);
});

dataset('payloads', function () {
    return [
        [[ 'selection_timer' => 'taco']],
        [[ 'selection_timer' => 301]],
        [[ 'selection_timer' => 59]]
    ];
});

it('will not allow strings for timers', function ($payload) {
    $game = $this->createGame();
    $user = $game->users()->first();
    $originalSettings = $game->setting->toArray();

    $this
        ->actingAs($user)
        ->postJson(route('api.game.settings.update', $game), $payload)
        ->assertUnprocessable();

    $game->refresh();
    expect($game->setting->selection_timer)->toEqual($originalSettings['selection_timer']);
})->with('payloads');
