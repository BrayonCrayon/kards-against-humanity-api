<?php

use App\Events\CardsSubmitted;
use App\Models\Game;

it('sends game id and user id', function () {
    $game = Game::factory()->create();

    $cardsSubmitted = new CardsSubmitted($game, $game->judge);

    $payload = [
        'gameId' => $game->id,
        'userId' => $game->judge->id,
    ];

    expect($cardsSubmitted->broadcastWith())->toEqual($payload);
});

it('broadcasts on correct channel name', function () {
    $game = Game::factory()->create();

    $cardsSubmitted = new CardsSubmitted($game, $game->judge);

    expect($cardsSubmitted->broadcastAs())->toEqual("cards.submitted");
});
