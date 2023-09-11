<?php

use App\Events\GameJoined;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use function Pest\Laravel\{actingAs, getJson};

uses(\Tests\Traits\GameUtilities::class);

it('does not allow non auth users', function () {
    $game = Game::factory()->create();
    expect(getJson(route('api.game.show', $game->id)))
        ->assertUnauthorized();
});

it('does not accept non existing id', function () {
    $user = User::factory()->create();
    expect(actingAs($user)
        ->getJson(route('api.game.show', $this->faker->uuid)))
        ->toBeNotFound();
});

it('returns current game state', function () {
    Event::fake([GameJoined::class]);

    $game = $this->createGame(4);
    $user = $game->nonJudgeUsers()->first();

    $response = $this->actingAs($user)
        ->getJson(route('api.game.show', $game->id))
        ->assertOk();

    $response->assertJsonFragment([
        'id' => $game->id,
        'name' => $game->name,
        'code' => $game->code,
        'redrawLimit' => $game->redraw_limit,
        'judgeId' => $game->judge_id
    ]);

    $response->assertJsonFragment([
        'hasSubmittedWhiteCards' => false
    ]);

    $response->assertJsonFragment([
        'submittedWhiteCardIds' => []
    ]);

    $response->assertJsonFragment([
        'id' => $game->blackCard->id,
        'pick' => $game->blackCard->pick,
        'text' => $game->blackCard->text,
    ]);

    $game->users->each(function ($player) use ($response) {
        $response->assertJsonFragment([
            'id' => $player->id,
            'name' => $player->name,
            'score' => $player->score,
            'hasSubmittedWhiteCards' => $player->hasSubmittedWhiteCards
        ]);
    });

    $game->refresh();
    expect($user->hand)->toHaveCount(Game::HAND_LIMIT);
    $user->hand->each(function ($userCard) use ($response) {
        $response->assertJsonFragment([
            'id' => $userCard->whiteCard->id,
            'text' => $userCard->whiteCard->text,
            'expansionId' => $userCard->whiteCard->expansion_id,
            'order' => $userCard->order,
            'selected' => $userCard->selected
        ]);
    });

    $loggedInUser = $game->getPlayer($user->id);
    $response->assertJsonFragment([
        'currentUser' => [
            'id' => $loggedInUser->id,
            'name' => $loggedInUser->name,
            'score' => $loggedInUser->score,
            'hasSubmittedWhiteCards' => $loggedInUser->hasSubmittedWhiteCards,
            'isSpectator' => $loggedInUser->gameState->is_spectator,
            'redrawCount' => $loggedInUser->gameState->redraw_count
        ]
    ]);
});
