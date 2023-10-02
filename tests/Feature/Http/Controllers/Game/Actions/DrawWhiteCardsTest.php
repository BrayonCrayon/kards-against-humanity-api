<?php

use App\Models\Game;
use App\Models\User;
use App\Models\WhiteCard;
use App\Services\GameService;
use Mockery\MockInterface;
use function Pest\Laravel\{actingAs, getJson};

test('user can draw more white cards', function () {
    $game = Game::factory()->has(User::factory())->create();
    $user = $game->players()->first();
    $expectedCard = WhiteCard::factory()->create();

    $this->mock(GameService::class)
        ->shouldReceive('drawWhiteCards')
        ->withArgs(function ($u, $g) use ($game, $user) {
            return $game->id === $g->id && $user->id === $u->id;
        })
        ->once()
        ->andReturn([$expectedCard]);

    expect(actingAs($user)->getJson(route('api.game.whiteCards.draw', $game)))
        ->toBeOk()
        ->toHaveJsonFragment([
            'id' => $expectedCard->id,
            'text' => $expectedCard->text,
            'expansionId' => $expectedCard->expansion_id,
        ]);
});

test('guests cannot draw cards', function () {
    $game = Game::factory()->has(User::factory())->create();

    $this->instance(
        GameService::class,
        Mockery::mock(GameService::class, function (MockInterface $mock) use ($game) {
            $mock->shouldReceive('drawWhiteCards')->never();
        })
    );

    expect(getJson(route('api.game.whiteCards.draw', $game)))->toBeUnauthorized();
});
