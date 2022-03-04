<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class KickPlayerControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

    }

    /** @test */
    public function it_will_not_allow_non_auth_users_to_kick_players()
    {
        $game = Game::factory()->hasUsers(2)->create();
        $player = $game->nonJudgeUsers()->first();
        $this->postJson(route('api.game.player.kick', [$game->id, $player->id]))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_will_not_allow_auth_user_to_kick_a_player_invalid_game()
    {
        $game = Game::factory()->hasUsers(2)->create();
        $player = $game->nonJudgeUsers()->first();
        $this->actingAs($game->judge)
            ->postJson(route('api.game.player.kick', [$this->faker->uuid, $player->id]))
            ->assertNotFound();
    }


}
