<?php

use App\Events\GameJoined;
use App\Models\GameUser;
use Illuminate\Support\Facades\Event;

uses(\Tests\Traits\GameUtilities::class);

beforeEach(function () {
    $this->game = $this->createGame();
});

it('will allow a user to spectate a game', function () {
    Event::fake();
    $this->postJson(route('api.game.spectate', $this->game->code))
        ->assertSuccessful();

    expect($this->game->spectators)->toHaveCount(1);
});

it('will return game data to spectator', function () {
    Event::fake();
    $blackCard = $this->game->blackCard;
    $response = $this->postJson(route('api.game.spectate', $this->game->code))
        ->assertSuccessful()
        ->assertJsonFragment([
                'game' => [
                    'code' => $this->game->code,
                    'id' => $this->game->id,
                    'judgeId' => $this->game->judge_id,
                    'name' => $this->game->name,
                    'redrawLimit' => $this->game->redraw_limit,
                    'selectionEndsAt' => $this->game->selection_ends_at,
                    'selectionTimer' => $this->game->setting->selection_timer,
                    'hasAnimations' => $this->game->setting->has_animations,
                ],
        ]);

    $this->game->users->each(function($user) use ($response) {
        $response->assertJsonFragment([
            'id' => $user->id,
            'name' => $user->name
        ]);
    });

    $spectateUser = GameUser::where('is_spectator', true)->where('game_id', $this->game->id)->first();

    $response->assertJsonFragment([
        'user' => [
            'id' => $spectateUser->user->id,
            'name' => $spectateUser->user->name,
            'isSpectator' => true,
            'redrawCount' => 0,
            'hasSubmittedWhiteCards' => $spectateUser->user->hasSubmittedWhiteCards,
            'score' => $spectateUser->user->score
        ]
    ]);
    $response->assertJsonFragment([
        'blackCard' => [
            'id' => $blackCard->id,
            'pick' => $blackCard->pick,
            'text' => $blackCard->text,
            'expansionId' => $blackCard->expansion_id,
        ]
    ]);
    Event::assertDispatched(GameJoined::class);
});
