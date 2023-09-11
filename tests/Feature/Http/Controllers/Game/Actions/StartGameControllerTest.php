<?php

use App\Events\RoundStart;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

uses(\Tests\Traits\GameUtilities::class);

it('starts a game', function () {
    Carbon::setTestNow(Carbon::now());
    Event::fake();
    $game = $this->createGame();
    $game->setting()->update([
        'selection_timer' => $this->faker->numberBetween(60,300)
    ]);

    $this->actingAs($game->judge)
        ->postJson(route('api.game.start', [$game->id]))
        ->assertSuccessful();

    $game->refresh();
    expect(Carbon::now()->unix() + $game->setting->selection_timer)->toEqual($game->selection_ends_at);
    Event::assertDispatched(RoundStart::class);
});

it('will reject non authed users', function () {
    $game = $this->createGame();

    $this->postJson(route('api.game.start', $game))
        ->assertUnauthorized();
});

it('will not allow user to start other games', function () {
    $game = $this->createGame();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('api.game.start', $game))
        ->assertForbidden();

    $this->assertDatabaseHas('games', [
        'id' => $game->id,
        'selection_ends_at' => null
    ]);
});

it('will only allow judge to start game', function () {
    $game = $this->createGame(2);
    $user = $game->nonJudgeUsers()->first();

    $this->actingAs($user)
        ->postJson(route('api.game.start', $game))
        ->assertForbidden();
});
