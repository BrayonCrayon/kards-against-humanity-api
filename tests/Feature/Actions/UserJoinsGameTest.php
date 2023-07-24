<?php

use App\Actions\CreatingUser;
use App\Actions\UserJoinsGame;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Support\Facades\Event;

uses(\Tests\Traits\GameUtilities::class);

it('creates a new user if the user belongs to another game', function () {
    Event::fake();
    $game = Game::factory()->hasUsers(1)->create();
    $otherGame = Game::factory()->create();
    $player = $game->nonJudgeUsers()->first();

    $this->actingAs($player);

    $userJoinsGame = new UserJoinsGame(new GameService(), new CreatingUser());
    $userJoinsGame($otherGame, $player->name);

    $this->assertNotEquals(auth()->user()->id, $player->id);
});

it('will allow an existing user to join a different game', function () {
    Event::fake();
    $game = $this->createGame();
    $user = User::factory()->create();

    $this->actingAs($user);
    (new UserJoinsGame(new GameService(), new CreatingUser()))($game, $user);

    $game->refresh();
    expect($game->players()->whereUserId($user->id)->first()->id)->toEqual($user->id);
});
