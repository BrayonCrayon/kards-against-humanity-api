<?php

use App\Models\Game;
use App\Models\UserGameWhiteCard;

uses(\Tests\Traits\GameUtilities::class);

it('will not allow non authed user to redraw', function () {
    $game = Game::factory()->create();

    $this->postJson(route('api.game.redraw', $game))
        ->assertUnauthorized();
});

it('will put players hand back into the deck', function () {
    $game = $this->createGame();
    $user = $game->nonJudgeUsers()->first();

    $this->actingAs($user)
        ->postJson(route('api.game.redraw', $game))
        ->assertOK();

    $previousCards = UserGameWhiteCard::query()->onlyTrashed()->get();
    expect($previousCards)->toHaveCount(0);
});

it('will redraw users hand', function () {
    $game = $this->createGame();

    $hand = $game->judge->hand->pluck('id');

    $this->actingAs($game->judge)
        ->postJson(route('api.game.redraw', $game))
        ->assertOK()
        ->assertJsonCount(Game::HAND_LIMIT, 'data');

    $newHand = $game->judge->hand()->get()->pluck('id');
    $this->assertNotEquals($newHand, $hand);
    $this->assertDatabaseHas('game_users', [
        'user_id' => $game->judge->id,
        'redraw_count' => 1,
    ]);
});

it('will not allow the user redraw past their limit', function () {
    $game = Game::factory()->create();
    $hand = $game->judge->hand->pluck('id');
    $user = $game->getPlayer($game->judge_id);
    $user->gameState->redraw_count = 2;
    $user->gameState->save();

    $this->actingAs($user)
        ->postJson(route('api.game.redraw', $game))
        ->assertForbidden();

    $newHand = $user->hand()->get()->pluck('id');
    expect($hand)->toEqual($newHand);
    $this->assertDatabaseHas('game_users', [
        'user_id' => $user->id,
        'redraw_count' => 2
    ]);
});
