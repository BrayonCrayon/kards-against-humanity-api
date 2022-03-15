<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RedrawHandTest extends TestCase
{

    /** @test */
    public function it_will_redraw_users_hand()
    {
        $game = Game::factory()->create();
        $hand = $game->judge->whiteCardsInGame->pluck('id');

        $this->actingAs($game->judge)
            ->postJson(route('api.game.redraw', $game))
        ->assertOK();

        $newHand = $game->judge->whiteCardsInGame()->get()->pluck('id');
        $this->assertNotEquals($newHand, $hand);
    }
}
