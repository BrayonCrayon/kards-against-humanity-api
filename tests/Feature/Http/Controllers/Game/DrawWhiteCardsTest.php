<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Models\UserGameWhiteCard;
use App\Models\WhiteCard;
use App\Services\GameService;
use Illuminate\Http\Response;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class DrawWhiteCardsTest extends TestCase
{
    /** @test */
    public function user_can_draw_more_white_cards()
    {
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

        $this->actingAs($user)
            ->getJson(route('api.game.whiteCards.draw', $game))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $expectedCard->id,
                'text' => $expectedCard->text,
                'expansionId' => $expectedCard->expansion_id,
            ]);
    }

    /** @test */
    public function guests_cannot_draw_cards()
    {
        $game = Game::factory()->has(User::factory())->create();

        $this->instance(
            GameService::class,
            Mockery::mock(GameService::class, function (MockInterface $mock) use ($game) {
                $mock->shouldReceive('drawWhiteCards')->never();
            })
        );

        $this->getJson(route('api.game.whiteCards.draw', $game))
            ->assertUnauthorized();
    }
}
