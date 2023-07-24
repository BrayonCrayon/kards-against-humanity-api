<?php

use App\Models\Game;
use App\Models\User;
use App\Services\GameService;

uses(\Tests\Traits\GameUtilities::class);

it('will allow user to leave a game', function () {
    $game = Game::factory()->hasBlackCards()->hasUsers()->create();
    $player = User::first();

    $this->actingAs($player)
        ->postJson(route('api.game.leave', $game->id))->assertOK();

    $this->assertDatabaseMissing('game_users', ['user_id'  => $player->id]);
});

it('will not allow non auth to leave a game', function () {
    $game = Game::factory()->create();
    $this->postJson(route('api.game.leave', $game->id))
        ->assertUnauthorized();
});

it('will switch judge when judge leaves', function () {
    $service = new GameService();
    $game = $this->createGame();
    $judge = $game->judge;
    $player = $service->nextJudge($game);

    $this->actingAs($judge)
        ->postJson(route('api.game.leave', $game->id))
        ->assertOK();

    $game->refresh();
    $this->assertNotEquals($judge->id, $game->judge->id);
    expect($game->judge->id)->toEqual($player->id);
});

it('will remove left users white cards', function () {
    $game = $this->createGame();
    $user = $game->nonJudgeUsers()->first();
    $this->selectCardsForUser($user, $game);

    $this->actingAs($user)
        ->postJson(route('api.game.leave', $game->id))
        ->assertOk();

    $this->assertDatabaseMissing('user_game_white_cards', [
        'user_id' => $user->id,
        'game_id' => $game->id
    ]);
});
