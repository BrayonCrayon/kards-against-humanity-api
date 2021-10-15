<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Tests\TestCase;

class GetGameStateControllerTest extends TestCase
{
    private GameService $gameService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gameService = new GameService();
    }

    /** @test */
    public function it_does_not_allow_non_auth_users()
    {
        $game = Game::factory()->create();
        $this->getJson(route('api.game.state', $game->id))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_does_not_accept_non_existing_id()
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->getJson(route('api.game.state', $this->faker->uuid))
            ->assertNotFound();
    }

    /** @test */
    public function it_returns_current_game_state()
    {
        $players = User::factory()->count(4)->create();
        /** @var Game */
        $game = $this->gameService->createGame($players->first(), [Expansion::first()->id]);

        $players->filter(fn($player) => $player->id !== $players->first()->id)
            ->each(fn($player) => $this->gameService->joinGame($game, $player));


        $response = $this->actingAs($players->first())
            ->getJson(route('api.game.state', $game->id))
            ->assertOk();

        $response->assertJsonFragment([
                'id' => $game->id,
                'name' => $game->name,
                'judge_id' => $game->judge_id,
        ]);

        $response->assertJsonFragment([
            'id' => $game->currentBlackCard->id,
            'pick' => $game->currentBlackCard->pick,
            'text' => $game->currentBlackCard->text,
        ]);

        $response->assertJsonFragment([
            'judge' => [
                'id' => $players->first()->id,
                'name' => $players->first()->name,
                'remember_token' => $players->first()->remember_token,
                'updated_at' => $players->first()->updated_at,
                'created_at' => $players->first()->created_at,
            ],
        ]);

        $players->each(function ($player) use ($response) {
            $response->assertJsonFragment([
               'id' => $player->id,
               'name' => $player->name,
            ]);
        });

        $players->first()->whiteCards->each(function ($whiteCard) use ($response) {
            $response->assertJsonFragment([
               'id' => $whiteCard->id,
               'text' => $whiteCard->text,
               'expansion_id' => $whiteCard->expansion_id
            ]);
        });
    }
}
