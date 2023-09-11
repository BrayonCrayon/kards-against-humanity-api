<?php

use App\Models\Game;
use App\Models\User;
use function Pest\Laravel\{actingAs, getJson};

uses(\Illuminate\Foundation\Testing\DatabaseTransactions::class);

it('requires a user to be authenticated', function () {
    $game = Game::factory()->create();
    expect(getJson(route('api.game.players.index', $game)))->toBeUnauthorized();
});

it('requires the user to be a player in the game', function () {
    $game = Game::factory()->create();
    $user = User::factory()->create();
    expect(actingAs($user)
        ->getJson(route('api.game.players.index', $game)))->toBeNotFound();
});

it('returns the players in the game', function () {
    $game = Game::factory()->hasUsers(3)->create();

    expect(actingAs($game->judge)->getJson(route('api.game.players.index', $game)))
        ->toBeOk()
        ->assertJsonCount($game->users()->count(), 'data')
        ->toHaveJsonStructure([
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
