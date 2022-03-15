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
        // arrange
        // create game, player
        $game = Game::factory()->create();
        $hand = $game->users()->first()->whiteCards->pluck('id');

        // act
        // hit redraw endpoint acting as player
        $this->actingAs($game->judge)
            ->postJson(route('api.game.redraw', $game))
        ->assertOK();

        // assert
        // the hand that they previously had is not the same as the one they have now.
        for($i = 0; $i < $hand->count(); $i++){
            $updatedHand = $game->users()->first()->whiteCards()->get();
            $this->assertNotEquals($hand[$i], $updatedHand);
        }
    }
}
