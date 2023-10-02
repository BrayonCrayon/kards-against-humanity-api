<?php

use App\Models\Game;
use App\Models\User;

uses(\Tests\Traits\GameUtilities::class);

beforeEach(function () {
    $this->game = $this->createGame();
    $this->user = $this->game->nonJudgeUsers()->first();
});

test('game relationship brings back game type', function () {
    $user = User::factory()
        ->hasGames(1)
        ->create();
    expect($user->games->first())->toBeInstanceOf(Game::class);
});

test('has submitted white cards attribute brings back true if user has submitted cards', function () {
    $this->selectAllPlayersCards($this->game);
    expect($this->user->hasSubmittedWhiteCards)->toBeTrue();
});

test('has submitted white cards attribute returns false when no cards are submitted', function () {
    expect($this->user->hasSubmittedWhiteCards)->toBeFalse();
});

it('returns submitted white card ids when user has submitted cards', function () {
    $this->selectAllPlayersCards($this->game);
    $submittedWhiteCardIds = $this->user->hand()->selected()->pluck('white_card_id');
    $submittedWhiteCardIds->each(fn ($cardId) => expect($this->user->submittedWhiteCardIds)->toContain($cardId));
});

test('submitted white cards returns empty array when there are no submitted cards', function () {
    expect($this->user->submittedWhiteCardIds)->toBeEmpty();
});

it('returns number of rounds user has won', function () {
    $this->selectAllPlayersCards($this->game);
    $this->submitPlayerForRoundWinner($this->user, $this->game);

    expect($this->user->score)->toEqual(1);
});

it('return score of zero if player has not won', function () {
    expect($this->user->score)->toEqual(0);
});
