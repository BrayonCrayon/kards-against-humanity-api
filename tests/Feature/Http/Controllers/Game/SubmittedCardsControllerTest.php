<?php

use App\Models\Game;
use App\Models\User;
use App\Services\GameService;

uses(\Tests\Traits\GameUtilities::class);

it('will not allow non auth user to get submitted cards', function () {
    $game = Game::factory()->create();
    $this->getJson(route('api.game.submitted.cards', $game->id))
        ->assertUnauthorized();
});

it('will not accept a game that does not exist', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->getJson(route('api.game.submitted.cards', $this->faker->uuid()))
        ->assertNotFound();
});

it('will return user submitted cards', function () {
    $game = $this->createGame();

    $submittedUser = $game->nonJudgeUsers->first();

    $this->selectAllPlayersCards($game);

    $this->actingAs($submittedUser)
        ->getJson(route('api.game.submitted.cards', $game->id))
        ->assertOK()
        ->assertJsonStructure([
            'data' => [
                [
                    'user_id',
                    'submitted_cards' => [
                        [
                            'id',
                            'text',
                            'expansionId',
                            'order'
                        ]
                    ]
                ]
            ]
        ]);
});

it('will bring back correct submitted user cards', function () {
    $game = $this->createGame();
    $submittedUser = $game->nonJudgeUsers->first();

    $this->selectAllPlayersCards($game);

    $response = $this->actingAs($submittedUser)
        ->getJson(route('api.game.submitted.cards', $game->id))
        ->assertOK();

    expect($response->json("data"))->toHaveCount(1);
    expect($response->json("data")[0]["submitted_cards"])->toHaveCount($submittedUser->hand()->selected()->count());
    $submittedUser->hand()->selected()->get()->each(function ($whiteCardInGame) use ($response) {
        $response->assertJsonFragment([
            'id' => $whiteCardInGame->white_card_id,
            'text' => $whiteCardInGame->whiteCard->text,
            'expansionId' => $whiteCardInGame->whiteCard->expansion_id,
            'order' => $whiteCardInGame->order
        ]);
    });
});

test('call get submitted cards from game service when getting submitted cards', function () {
    $game = $this->createGame();
    $submittedUser = $game->nonJudgeUsers->first();

    $this->selectAllPlayersCards($game);

    $spy = $this->spy(GameService::class);

    $this->actingAs($submittedUser)
        ->getJson(route('api.game.submitted.cards', $game->id))
        ->assertOK();

    $spy->shouldHaveReceived("getSubmittedCards")
        ->withArgs(function($argument) use ($game) {
            return $argument->id === $game->id;
        })
        ->once();
});
