<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RedrawControllerTest extends TestCase
{

    /** @test */
    public function it_will_not_allow_non_authed_user_to_redraw()
    {
        $game = Game::factory()->create();

        $this->postJson(route('api.game.redraw', $game))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_will_redraw_users_hand()
    {
        $game = Game::factory()->create();
        $hand = $game->judge->whiteCardsInGame->pluck('id');

        $this->actingAs($game->judge)
            ->postJson(route('api.game.redraw', $game))
            ->assertOK()
            ->assertJsonCount(7, 'data');

        $newHand = $game->judge->whiteCardsInGame()->get()->pluck('id');
        $this->assertNotEquals($newHand, $hand);
        $this->assertDatabaseHas('game_users', [
            'user_id' => $game->judge->id,
            'redraw_count' => 1,
        ]);
    }

    /** @test */
    public function it_will_not_allow_the_user_redraw_past_their_limit()
    {
        $game = Game::factory()->create();
        $hand = $game->judge->whiteCardsInGame->pluck('id');
        $user = $game->getUser($game->judge_id);
        $user->gameState->redraw_count = 2;
        $user->gameState->save();

        $this->actingAs($user)
            ->postJson(route('api.game.redraw', $game))
            ->assertForbidden();

        $newHand = $user->whiteCardsInGame()->get()->pluck('id');
        $this->assertEquals($newHand, $hand);
        $this->assertDatabaseHas('game_users', [
            'user_id' => $user->id,
            'redraw_count' => 2
        ]);
    }
}
