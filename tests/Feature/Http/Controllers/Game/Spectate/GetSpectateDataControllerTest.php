<?php

use App\Models\User;

uses(\Tests\Traits\GameUtilities::class);

it('will reject non auth users', function () {
    $this->getJson(route('api.game.spectate.show', $this->faker->randomNumber()))
        ->assertUnauthorized();
});

it('will return spectation state', function () {
    $game = $this->createGame();
    $user = User::factory()->create();
    $game->users()->attach($user->id, ['is_spectator' => true]);

    $this->actingAs($user)
        ->getJson(route('api.game.spectate.show', $game))
        ->assertOk()
        ->assertJsonCount(4, 'data')
        ->assertJsonCount($game->players->count(), 'data.users')
        ->assertJsonFragment([
            'game' => [
                'id' => $game->id,
                'name' => $game->name,
                'judgeId' => $game->judge_id,
                'code' => $game->code,
                'redrawLimit' => $game->redraw_limit,
                'selectionEndsAt' => $game->selection_ends_at,
                'selectionTimer' => $game->setting->selection_timer
            ]
        ])->assertJsonFragment([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'redrawCount' => 0,
                'isSpectator' => true,
                'score' => 0,
                'hasSubmittedWhiteCards' => false
            ]
        ])
        ->assertJsonFragment([
            'blackCard' => [
                'id' => $game->blackCard->id,
                'pick' => $game->blackCard->pick,
                'text' => $game->blackCard->text,
                'expansionId' => $game->blackCard->expansion_id,
            ]
        ]);
});
