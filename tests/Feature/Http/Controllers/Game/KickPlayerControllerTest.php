<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class KickPlayerControllerTest extends TestCase
{
    private $game;
    protected function setUp(): void
    {
        parent::setUp();
        $this->game = Game::factory()->hasUsers(2)->create();
    }

    /** @test */
    public function it_will_not_allow_non_auth_users_to_kick_players()
    {
        $player = $this->game->nonJudgeUsers()->first();
        $this->postJson(route('api.game.player.kick', [$this->game->id, $player->id]))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_will_not_allow_auth_user_to_kick_a_player_invalid_game()
    {
        $player = $this->game->nonJudgeUsers()->first();
        $this->actingAs($this->game->judge)
            ->postJson(route('api.game.player.kick', [$this->faker->uuid, $player->id]))
            ->assertNotFound();
    }

    /** @test */
    public function it_will_reject_invalid_player_ids()
    {
        $this->actingAs($this->game->judge)
            ->postJson(route('api.game.player.kick', [$this->game->id, 0]))
            ->assertNotFound();
    }

    /** @test */
    public function it_will_reject_non_judge_players_from_kicking_users()
    {
        $player = $this->game->nonJudgeUsers()->first();
        $this->actingAs($player)
            ->postJson(route('api.game.player.kick', [$this->game, $player]))
            ->assertForbidden();
    }


}
