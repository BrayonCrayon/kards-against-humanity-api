<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PlayersControllerTest extends TestCase
{

    use DatabaseTransactions;

    /** @test */
    public function it_requires_a_user_to_be_authenticated()
    {
        $game = Game::factory()->create();
        $this->getJson(route('api.game.players.index', $game))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_requires_the_user_to_be_a_player_in_the_game()
    {
        $game = Game::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user)
            ->getJson(route('api.game.players.index', $game))
            ->assertNotFound();
    }

    /** @test */
    public function it_returns_the_players_in_the_game()
    {
        $game = Game::factory()->hasUsers(3)->create();

        $this->actingAs($game->judge)
            ->getJson(route('api.game.players.index', $game))
            ->assertOk()
            ->assertJsonCount($game->users()->count(), 'data')
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'has_submitted_white_cards',
                        'score',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }
}
