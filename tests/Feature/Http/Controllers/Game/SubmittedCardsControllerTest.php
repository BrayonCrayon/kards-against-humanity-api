<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SubmittedCardsControllerTest extends TestCase
{

    /** @test */
    public function it_will_not_allow_non_auth_user_to_get_submitted_cards()
    {
        $game = Game::factory()->create();
        $this->getJson(route('api.game.submitted.cards', $game->id))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_will_not_accept_a_game_that_does_not_exist()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('api.game.submitted.cards', $this->faker->uuid()))
            ->assertNotFound();
    }
}
