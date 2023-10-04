<?php

use App\Events\GameJoined;
use App\Events\WinnerSelected;
use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Models\UserGameWhiteCard;
use App\Services\GameService;
use App\Services\HelperService;
use Illuminate\Support\Facades\Event;

uses(\Tests\Traits\GameUtilities::class);

beforeEach(function () {
    $this->helperService = new HelperService();
    $this->gameService = new GameService();
});

it('does not draw white cards that have already been drawn', function () {
    $game = Game::factory()
        ->has(Expansion::factory()->hasWhiteCards(8)->hasBlackCards(1))
        ->hasUsers()
        ->create();
    $user = $game->nonJudgeUsers()->first();
    $this->drawCardsForUser($user, $game);
    $user->refresh();
    $lastRemainingCard = $game->expansions()
        ->first()
        ->whiteCards()
        ->whereNotIn('id', $user->hand->pluck('white_card_id'))
        ->first();
    $user->hand->firstOrFail()->delete();

    $pickedCards = $this->gameService->drawWhiteCards($user, $game);

    expect($pickedCards)->toHaveCount(1);
    expect($pickedCards->first()->id)->toEqual($lastRemainingCard->id);
});

it('calculates the number of cards to draw based on how many cards are in the users hand', function () {
    $game = Game::factory()
        ->has(Expansion::factory()->hasWhiteCards(10)->hasBlackCards(1))
        ->hasUsers()
        ->create();
    $user = $game->judge;
    $this->drawCardsForUser($user, $game);

    $user->hand()->limit(4)->delete();

    $drawnCards = $this->gameService->drawWhiteCards($user, $game);

    expect($drawnCards)->toHaveCount(3);
});

it('only draws available black cards', function () {
    $game = $this->createGame();
    $this->drawBlackCard($game);
    $game->gameBlackCards()->delete();
    $remainingCard = BlackCard::factory()->create(['expansion_id' => $game->expansions->first()->id]);

    $drawnCard = $this->gameService->drawBlackCard($game);

    expect($drawnCard->id)->toEqual($remainingCard->id);
});

it('emits an event when a user joins a game', function () {
    $game = $this->createGame();
    Event::fake();

    /** @var User $user */
    $user = User::factory()->create();

    $this->gameService->joinGame($game, $user);

    Event::assertDispatched(GameJoined::class, function (GameJoined $event) use ($user, $game) {
        return $event->game->id === $game->id && $user->id === $event->user->id;
    });
});

it('emits an event when judge user selects a round winner', function () {
    Event::fake();
    $game = $this->createGame();
    $user = $game->nonJudgeUsers()->first();

    $this->gameService->selectWinner($game, $user);

    Event::assertDispatched(WinnerSelected::class, function (WinnerSelected $event) use ($user, $game) {
        return $event->user->id === $user->id && $event->game->id === $game->id;
    });
});

test('calling get submitted card brings back all submitted cards', function () {
    $game = Game::factory()
        ->has(Expansion::factory()->hasWhiteCards(7)->has(BlackCard::factory()->pickOf2()))
        ->hasUsers()
        ->create();
    $user = $game->nonJudgeUsers()->first();
    $this->drawBlackCard($game);
    $this->drawCardsForUser($user, $game, 7);
    $this->selectCardsForUser($user, $game);
    $whiteCardIds = $user->hand()->whereSelected(true)->pluck('white_card_id');

    $data = $this->gameService->getSubmittedCards($game);
    $submittedData = $data->first();

    expect($data)->toHaveCount(1);
    expect($submittedData["user_id"])->toEqual($user->id);
    expect($submittedData["submitted_cards"])->toHaveCount(2);
    $submittedData["submitted_cards"]->each(function ($item) use ($user, $whiteCardIds) {
        expect($whiteCardIds->contains($item->white_card_id))->toBeTrue();
    });
});

it('will bring back latest round winner data', function () {
    $game = $this->createGame();
    $user = $game->nonJudgeUsers()->first();
    $this->selectAndSubmitPlayerForRoundWinner($user, $game);
    $pickedCards = $user->hand()->whereSelected(true)->get();

    $winnerData = $this->gameService->roundWinner($game, $game->blackCard);

    expect($winnerData['user']['id'])->toEqual($user->id);
    expect($winnerData['userGameWhiteCards'])->toHaveCount($pickedCards->count());
    $winnerData['userGameWhiteCards']->each(function ($whiteCard) use ($pickedCards) {
        expect($whiteCard)->toBeInstanceOf(UserGameWhiteCard::class);
        expect($pickedCards->pluck('id')->contains($whiteCard['id']))->toBeTrue();
    });
});

it('will bring back round winner data from a previous round', function () {
    Event::fake();
    $game = Game::factory()
        ->hasSetting()
        ->has(Expansion::factory()->hasWhiteCards(7)->hasBlackCards(2))
        ->hasUsers()
        ->create();
    $this->drawBlackCard($game);
    $user = $game->nonJudgeUsers()->first();
    $this->drawCardsForUser($user, $game,7);
    $this->selectAndSubmitPlayerForRoundWinner($user, $game);
    $previousBlackCard = $game->blackCard;
    $selectedWhiteCardCount = $user->hand()->whereSelected(true)->count();
    $this->gameService->rotateGame($game);

    $winnerData = $this->gameService->roundWinner($game, $previousBlackCard);

    expect($winnerData['user']['id'])->toEqual($user->id);
    expect($winnerData['userGameWhiteCards'])->toHaveCount($selectedWhiteCardCount);
});

it('will bring back correct card amount for each user after game rotate', function () {
    Event::fake();
    $game = Game::factory()
        ->hasSetting()
        ->has(Expansion::factory()->hasBlackCards(2)->hasWhiteCards(14))
        ->hasUsers(1)
        ->create();
    $playerWinner = $game->nonJudgeUsers()->first();
    $this->drawBlackCard($game);
    $this->selectAndSubmitPlayerForRoundWinner($playerWinner, $game);

    $this->gameService->rotateGame($game);

    $playerWinner = $playerWinner->refresh();
    expect($playerWinner->hand)->toHaveCount(Game::HAND_LIMIT);
});

it('will return randomize submitted cards', function () {
    $game = Game::factory()->has(Expansion::factory()->hasBlackCards()->hasWhiteCards(7))->hasUsers(4)->create();
    $this->drawBlackCard($game);
    $this->selectAllPlayersCards($game);

    $result = $this->gameService->getSubmittedCards($game);
    $responseUserIds = $result->pluck('user_id');

    $orderCount = 0;
    $previousId = 0;
    $responseUserIds->each(function ($item) use (&$orderCount, &$previousId) {

        if ($previousId and $previousId < $item) {
            $orderCount += 1;
        }
        $previousId = $item;
    });

    expect($result->pluck('submitted_cards'))->toHaveCount($game->blackCardPick * 4);
    $this->assertNotEquals($responseUserIds, $orderCount);
});

it('will reset draw count for all players', function () {
    $game = Game::factory()->hasUsers(4)->create();

    $game->nonJudgeUsers->each(function ($user) {
        $user->gameState->redraw_count = 2;
        $user->gameState->save();
    });

    $this->gameService->resetDrawCount($game);

    $game->nonJudgeUsers->each(function ($user) {
        $user->gameState->refresh();
        expect($user->gameState->redraw_count)->toEqual(0);
    });
});

it('will find next judge', function () {
    $game = $this->createGame(2);
    $currentJudgeIndex = $game->players->pluck('user.id')->search($game->judge_id);
    $nextJudge = $game->players[($currentJudgeIndex + 1) % $game->players->count()];

    $user = $this->gameService->nextJudge($game);

    $this->assertNotEquals($user->id, $game->judge_id);
    expect($user->id)->toEqual($nextJudge->id);
});

it('will set selection_ends_at to null when game rotates', function () {
    Event::fake();
    $game = $this->createGame(2, 2);
    $game->setting()->update([
        'selection_timer' => 60
    ]);

    $this->gameService->rotateGame($game);

    expect($game->refresh()->selection_ends_at)->toBeNull();
});
