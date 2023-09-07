<?php

use App\Events\CardsSubmitted;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;

uses(\Tests\Traits\GameUtilities::class);

beforeEach(function () {
    $this->game = $this->createGame(blackCardPick: 2);
    $this->user = $this->game->judge;
    $this->route = "api.game.select";
});

test('user cannot select a card that does not exit', function () {
    $invalid_card_id = 99999999;

    $this->actingAs($this->user)->postJson(route($this->route, $this->game->id), [
        'whiteCardIds' => [$invalid_card_id],
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('user submits cards for a game', function () {
    Event::fake();
    $cards = $this->user->hand->take(2);

    $this->actingAs($this->user)->postJson(route($this->route, $this->game->id), [
        'whiteCardIds' => $cards->pluck('white_card_id')->toArray(),
    ])->assertNoContent();

    $cards->each(function ($card) {
        $card->refresh();
        expect($card->selected)->toBeTrue();
    });
});

test('user cannot submit more cards than the black card pick', function () {
    $blackCardPick = $this->game->blackCard->pick;
    $ids = $this->user->hand->pluck('white_card_id')->toArray();

    $this->actingAs($this->user)->postJson(route($this->route, $this->game->id), [
        'whiteCardIds' => $ids,
        'submitAmount' => $blackCardPick
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('user cannot submit less cards than the black card pick', function () {
    $blackCardPick = $this->game->blackCard->pick;
    $ids = $this->user->hand->pluck('white_card_id')->take($blackCardPick - 1);

    $this->actingAs($this->user)->postJson(route($this->route, $this->game->id), [
        'whiteCardIds' => $ids,
        'submitAmount' => $blackCardPick
    ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test('event fired when user submits cards for a game', function () {
    Event::fake();
    $cards = $this->user->hand->take(2);

    $this->actingAs($this->user)->postJson(route($this->route, $this->game->id), [
        'whiteCardIds' => $cards->pluck('white_card_id')->toArray(),
        'submitAmount' => $this->game->blackCard->pick
    ])->assertNoContent();

    Event::assertDispatched(CardsSubmitted::class, function (CardsSubmitted $event) {
        return $event->game->id === $this->game->id
            && $event->broadcastOn()->name === 'game-' . $this->game->id
            && $this->user->id === $event->user->id;
    });
});

test('user submitting cards will keep the order they were submitted in', function () {
    Event::fake();
    $cardsToSubmit = $this->user->hand->take(2);

    $this->actingAs($this->user)
        ->postJson(route($this->route, $this->game->id), [
            'whiteCardIds' => $cardsToSubmit->pluck('white_card_id')->toArray(),
            'submitAmount' => $this->game->blackCard->pick
        ])->assertNoContent();

    $orderNum = 1;
    $cardsToSubmit->each(function($submittedCard) use (&$orderNum) {
        $this->assertDatabaseHas('user_game_white_cards', [
            'id' => $submittedCard->id,
            'order' => $orderNum
        ]);
        ++$orderNum;
    });
});
