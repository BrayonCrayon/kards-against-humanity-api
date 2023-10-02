<?php

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameBlackCards;
use App\Models\User;
use App\Models\UserGameWhiteCard;
use App\Models\WhiteCard;

test('user relationship brings back user type', function () {
    $game = Game::factory()
        ->hasUsers(1)
        ->create();
    expect($game->users->first())->toBeInstanceOf(User::class);
});

test('expansion relationship brings back expansion type', function () {
    $game = Game::factory()
        ->hasAttached(Expansion::factory())
        ->create();
    expect($game->expansions->first())->toBeInstanceOf(Expansion::class);
});

it('has a judge', function () {
    $game = Game::factory()->create();

    expect($game->judge)->toBeInstanceOf(User::class);
});

it('can get a black card', function () {
    $blackCard = BlackCard::factory()->create();
    $game = Game::factory()->hasUsers(2)->create();

    GameBlackCards::create([
        'black_card_id' => $blackCard->id,
        'game_id' => $game->id,
    ]);

    expect($game->blackCard)->toBeInstanceOf(BlackCard::class);
});

it('can get black cards', function () {
    $game = Game::factory()->create();

    $blackCard = BlackCard::factory()->create();
    $game->blackCards()->attach($blackCard);

    expect($game->blackCards->first()->id)->toEqual($blackCard->id);
});

it('brings back users that are not a judge user', function () {
    $usersToCreate = 3;
    $game = Game::factory()->hasUsers($usersToCreate)->create();

    $users = $game->nonJudgeUsers()->get()->pluck('id');

    expect($users)->toHaveCount($usersToCreate);
    expect(in_array($game->judge_id, $users->toArray()))->toBeFalse();
});

it('returns correct black pick amount from game attribute', function () {
    $game = Game::factory()->hasBlackCards()->create();

    expect($game->blackCardPick)->toEqual($game->blackCard->pick);
});

it('can get white cards', function () {
    $game = Game::factory()->has(Expansion::factory()->hasWhiteCards())->create();

    $whiteCard = WhiteCard::firstOrFail();

    expect($game->available_white_cards->first()->id)->toEqual($whiteCard->id);
});

it('excludes white cards that have been drawn', function () {
    $game = Game::factory()->has(Expansion::factory()->hasWhiteCards(2))->create();

    [$whiteCard, $drawnWhiteCard] = WhiteCard::all();

    UserGameWhiteCard::create([
        'white_card_id' => $drawnWhiteCard->id,
        'user_id' => $game->judge->id,
        'game_id' => $game->id,
    ]);

    expect($game->available_white_cards)->toHaveCount(1);
    //        $this->assertEquals($whiteCard->id, $game->available_white_cards->first()->id);
});
