<?php

use App\Models\Game;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

it('requires a user to be authenticated', function () {
    $game = Game::factory()->create();
    $this->getJson(route('api.game.players.index', $game))
        ->assertUnauthorized();
});

it('requires the user to be a player in the game', function () {
    $game = Game::factory()->create();
    $user = User::factory()->create();
    $this->actingAs($user)
        ->getJson(route('api.game.players.index', $game))
        ->assertNotFound();
});

it('returns the players in the game', function () {
    $game = Game::factory()->hasUsers(3)->create();

    $this->actingAs($game->judge)
        ->getJson(route('api.game.players.index', $game))
        ->assertOk()
        ->assertJsonCount($game->users()->count(), 'data')
        ->assertJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'hasSubmittedWhiteCards',
                    'score'
                ]
            ]
        ]);
});
