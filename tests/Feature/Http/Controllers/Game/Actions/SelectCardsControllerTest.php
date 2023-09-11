<?php

use App\Events\RoundStart;
use Illuminate\Support\Carbon;

uses(\Tests\Traits\GameUtilities::class);

it('will start the judge round when the last player selects their cards', function () {
    $this->markTestSkipped('Pending player reaction to selection timer');
    Carbon::setTestNow();
    $game = $this->createGame(2);
    $game->setting->update([
        'selection_timer' => 60,
    ]);
    [$player1, $player2] = $game->nonJudgeUsers->all();
    $this->selectCardsForUser($player1, $game);
    $cardsToSelect = $player2->whiteCards->take($game->blackCardPick)->pluck('id');

    $this->expectsEvents(RoundStart::class);

    $this->actingAs($player2)
        ->postJson(route('api.game.select', $game), ['whiteCardIds' => $cardsToSelect])
        ->assertSuccessful();

    expect($game->refresh()->selection_ends_at)->toEqual(now()->unix() + 60);
});

it('will only start a new round when last player selects their card', function () {
    $this->markTestSkipped('Pending player reaction to selection timer');
    $game = $this->createGame(2);
    $player1 = $game->users()->first();

    $cardsToSelect = $player1->whiteCards->take($game->blackCardPick)->pluck('id');

    $this->doesntExpectEvents(RoundStart::class);

    $this->actingAs($player1)
        ->postJson(route('api.game.select', $game), ['whiteCardIds' => $cardsToSelect])
        ->assertSuccessful();
});

it('will not set selection ends at when no timer settings is set', function () {
    $this->markTestSkipped('Pending player reaction to selection timer');
    $game = $this->createGame(2);
    [$player1, $player2] = $game->nonJudgeUsers->all();
    $this->selectCardsForUser($player1, $game);
    $cardsToSelect = $player2->whiteCards->take($game->blackCardPick)->pluck('id');

    $this->doesntExpectEvents(RoundStart::class);

    $this->actingAs($player2)
        ->postJson(route('api.game.select', $game), ['whiteCardIds' => $cardsToSelect])
        ->assertSuccessful();

    expect($game->refresh()->selection_ends_at)->toEqual(null);
});
