<?php

use App\Models\Expansion;
use App\Models\Game;
use App\Models\UserGameWhiteCard;
use App\Services\GameService;
use Pest\Laravel\actingAs;
use Pest\Laravel\postJson;
use function Pest\Laravel\{actingAs, postJson};

uses(\Tests\Traits\GameUtilities::class);

beforeEach(function () {
    $this->gameService = new GameService();
    $this->game = Game::factory()->hasUsers(2)->create();
});

it('will not allow non auth users to kick players', function () {
    $player = $this->game->nonJudgeUsers()->first();
    expect(postJson(route('api.game.player.kick', [$this->game->id, $player->id])))
        ->toBeUnauthorized();
});

it('will not allow auth user to kick a player invalid game', function () {
    $player = $this->game->nonJudgeUsers()->first();
    expect(actingAs($this->game->judge)
        ->postJson(route('api.game.player.kick', [$this->faker->uuid, $player->id])))
        ->toBeNotFound();
});

it('will reject invalid player ids', function () {
    expect(actingAs($this->game->judge)
        ->postJson(route('api.game.player.kick', [$this->game->id, 0])))
        ->toBeNotFound();
});

it('will reject non judge players from kicking users', function () {
    $player = $this->game->nonJudgeUsers()->first();
    expect(actingAs($player)
        ->postJson(route('api.game.player.kick', [$this->game, $player])))
        ->toBeForbidden();
});

it('will reject judge players to kick other players of another game', function () {
    $differentGame = Game::factory()->create();
    $playerToKick = $this->game->nonJudgeUsers()->first();
    expect(actingAs($differentGame->judge)
        ->postJson(route('api.game.player.kick', [$this->game, $playerToKick])))
        ->toBeForbidden();
});

it('will kick player from game', function () {
    $game = Game::factory()
        ->has(Expansion::factory()->hasWhiteCards(21)->hasBlackCards(1))
        ->hasUsers(2)
        ->create();
    $this->drawBlackCard($game);
    $game->players()->each(fn ($user) => $this->gameService->drawWhiteCards($user, $game));
    $playerToKick = $game->nonJudgeUsers()->first();
    $playerCount = $game->users()->count();

    expect(actingAs($game->judge)
        ->postJson(route('api.game.player.kick', [$game, $playerToKick])))
        ->toBeOk();
    $game->refresh();

    expect($game->users)
        ->toHaveCount($playerCount - 1)
        ->and(UserGameWhiteCard::whereGameId($game->id)->get())
        ->toHaveCount($game->players->count() * Game::HAND_LIMIT);
});
