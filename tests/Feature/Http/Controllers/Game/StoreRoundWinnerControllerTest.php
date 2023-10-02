<?php

use App\Models\Game;
use App\Models\RoundWinner;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Support\Facades\Event;

uses(\Tests\Traits\GameUtilities::class);

beforeEach(function () {
    Event::fake();
});

it('does not allow game that does not exist', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->postJson(route('api.game.winner', $this->faker->uuid), [
        'user_id' => $user->id,
    ])->assertNotFound();
});

it('does not allow non authed user', function () {
    $game = Game::factory()->create();
    $this->postJson(route('api.game.winner', $game->id), [
        'user_id' => $game->judge->id,
    ])->assertUnauthorized();
});

it('stores game winner will data', function () {
    $game = $this->createGame();
    $this->drawBlackCard($game);
    $player = $game->nonJudgeUsers()->first();

    $this->selectAllPlayersCards($game);

    $this->actingAs($game->judge)->postJson(route('api.game.winner', $game->id), [
       'user_id' => $player->id,
    ])->assertOk();

    $roundWinners = RoundWinner::where('user_id', $player->id)->get();
    expect($roundWinners)->toHaveCount($game->blackCard->pick);
});

it('calls select winner from game service', function () {
    $game = $this->createGame();
    $this->drawBlackCard($game);

    $player = $game->nonJudgeUsers()->first();

    // TODO: rename playerSelectsCardsForSubmission instead of playerSubmitCards
    $this->selectAllPlayersCards($game);
    $gameServiceSpy = $this->spy(GameService::class);

    $this->actingAs($game->judge)
        ->postJson(route('api.game.winner', $game), [
            'user_id' => $player->id
        ])->assertOk();

    $gameServiceSpy->shouldHaveReceived('selectWinner')
        ->withArgs(function($gameArg, $userArg) use ($game, $player) {
            return $gameArg->id === $game->id && $userArg->id === $player->id;
        })
        ->once();
});
