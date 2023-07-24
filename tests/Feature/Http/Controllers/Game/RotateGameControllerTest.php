<?php

use App\Events\GameRotation;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Models\UserGameWhiteCard;
use App\Services\GameService;
use Illuminate\Support\Facades\Event;

uses(\Tests\Traits\GameUtilities::class);

beforeEach(function () {
    Event::fake();
    $this->gameService = new GameService();

    $this->game = Game::factory()
        ->has(Expansion::factory()->hasWhiteCards(4 * Game::HAND_LIMIT)->hasBlackCards(10))
        ->hasUsers(2)
        ->create();

    $this->drawBlackCard($this->game);
});

test('rotating changes current judge to new user', function () {
    $blackCardPick = $this->game->blackCard->pick;

    $firstJudge = $this->game->judge;
    $this->selectAllPlayersCards($this->game);

    $this->actingAs($firstJudge)->postJson(route('api.game.rotate', $this->game->id))->assertOk();

    $this->game->refresh();
    $this->assertNotEquals($firstJudge->id, $this->game->judge_id);
});

it('cycles through the users when assigning the judge when number of users is even', function () {
    $newUser = User::factory()->create();
    $this->gameService->joinGame($this->game, $newUser);

    $pickedJudgeIds = collect();

    $this->game->refresh();

    $this->game->users->each(fn($user) => $pickedJudgeIds->add(getNextJudge($user, $this->game)));

    expect($pickedJudgeIds->unique()->all())->toHaveCount($this->game->users->count());
});

it('gives new black card after game rotation', function () {
    $previousBlackCard = $this->game->blackCard;

    $this->selectAllPlayersCards($this->game);

    $this->actingAs($this->game->judge)->postJson(route('api.game.rotate', $this->game->id))->assertOk();

    $this->game->refresh();
    $this->assertNotEquals($this->game->blackCard->id, $previousBlackCard->id);
});

it('soft deletes all submitted white cards', function () {
    $this->selectAllPlayersCards($this->game);

    $selectedWhiteCards = UserGameWhiteCard::whereGameId($this->game->id)->where('selected', true)->get();

    $this->actingAs($this->game->judge)->postJson(route('api.game.rotate', $this->game->id))->assertOk();

    $selectedWhiteCards->each(fn ($selectedCard) => $this->assertSoftDeleted(UserGameWhiteCard::class, [
        'id' => $selectedCard->id,
    ]));
});

it('emits event with new white cards after game rotation', function () {
    $this->selectAllPlayersCards($this->game);

    $this->actingAs($this->game->users->first())
        ->postJson(route('api.game.rotate', $this->game->id))
        ->assertOk();

    Event::assertDispatched(GameRotation::class, function (GameRotation $event) {
        return $event->game->id === $this->game->id
            && $event->broadcastOn()->name === 'game-' . $this->game->id;
    });
});

it('calls game service to rotate game', function () {
    $serviceSpy = $this->spy(GameService::class);

    $this->selectAllPlayersCards($this->game);

    $this->actingAs($this->game->users->first())
        ->postJson(route('api.game.rotate', $this->game->id))
        ->assertOk();

    $serviceSpy->shouldHaveReceived('rotateGame')
        ->withArgs(function ($game) {
            return $game->id === $this->game->id;
        })
        ->once();
});

function getNextJudge($user, $game) : int
{
    $game->users->where('id', '<>', $game->judge->id)
        ->each(fn($user) => $this->gameService->selectCards($user->whiteCards->take($game->blackCard->pick)->pluck('id'), $game, $user));

    $this->actingAs($user)->postJson(route('api.game.rotate', $game->id))->assertOk();

    $game->refresh();

    $this->assertNotEquals($user->id, $game->judge->id);
    return $game->judge->id;
}
