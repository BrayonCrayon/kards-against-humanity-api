<?php

use App\Models\Game;
use App\Services\GameService;

uses(\Tests\Traits\GameUtilities::class);

beforeEach(function () {
    $this->game = $this->createGame(blackCardCount: 10);
    $this->user = $this->game->nonJudgeUsers()->first();
    $this->selectAllPlayersCards($this->game);
    $this->submitPlayerForRoundWinner($this->user, $this->game);
});

it('will call game service to retrieve round winner', function () {
    $serviceSpy = $this->spy(GameService::class);
    $serviceSpy->shouldReceive('roundWinner')
        ->andReturn([
           'user' => $this->user,
           'userGameWhiteCards' => $this->user->hand()->whereSelected(true)->get()
        ]);

    $this->actingAs($this->user)
        ->getJson(route('api.game.round.winner', [
            $this->game,
            $this->game->blackCard
        ]))
        ->assertOk();

    $serviceSpy->shouldHaveReceived('roundWinner')
        ->withArgs(function($game, $blackCard) {
            return $game->id === $this->game->id && $blackCard->id === $this->game->blackCard->id;
        })
        ->once();
});

it('returns the round winner', function () {
    $this->actingAs($this->user)->getJson(route('api.game.round.winner', [$this->game->id, $this->game->blackCard->id]))
        ->assertOK()
        ->assertJsonStructure([
            'data' => [
                'user_id',
                'submitted_cards' => [
                    [
                        'id',
                        'text',
                        'expansionId',
                        'order',
                    ]
                ],
                'black_card' => [
                    'id',
                    'pick',
                    'text',
                    'expansionId'
                ]
            ]
        ]);
});

it('returns a 403 when the user is not in the game', function () {
    $secondGame = Game::factory()->create();
    $this->actingAs($secondGame->judge)->getJson(route('api.game.round.winner', [$this->game->id, $this->game->blackCard->id]))
        ->assertForbidden();
});

it('returns 401 if unauthorized', function () {
    $this->getJson(route('api.game.round.winner', [$this->game->id, $this->game->blackCard->id]))
        ->assertUnauthorized();
});
