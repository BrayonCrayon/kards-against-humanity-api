<?php

use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use function Pest\Laravel\{actingAs, postJson};

uses(\Tests\Traits\GameUtilities::class);

it('will allow user to leave a game', function () {
    $game = Game::factory()->hasBlackCards()->hasUsers()->create();
    $player = User::first();

    expect(actingAs($player)->postJson(route('api.game.leave', $game->id)))
        ->toBeOk()
        ->and(['user_id' => $player->id])->not->toBeInDatabase('game_users');
});

it('will not allow non auth to leave a game', function () {
    $game = Game::factory()->create();
    expect(postJson(route('api.game.leave', $game->id)))->toBeUnauthorized();
});

it('will switch judge when judge leaves', function () {
    $service = new GameService();
    $game = $this->createGame();
    $judge = $game->judge;
    $player = $service->nextJudge($game);

    expect(actingAs($judge)->postJson(route('api.game.leave', $game->id)))->toBeOk();

    $game->refresh();
    expect($judge->id)->not->toEqual($game->judge->id)
        ->and($game->judge->id)->toEqual($player->id);
});

it('will remove left users white cards', function () {
    $game = $this->createGame();
    $user = $game->nonJudgeUsers()->first();
    $this->selectCardsForUser($user, $game);

    expect(actingAs($user)->postJson(route('api.game.leave', $game->id)))->toBeOk()
        ->and(['user_id' => $user->id, 'game_id' => $game->id])->not()->toBeInDatabase('user_game_white_cards');
});
