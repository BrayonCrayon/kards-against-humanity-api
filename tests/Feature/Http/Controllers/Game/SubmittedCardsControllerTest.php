<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
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

    /** @test */
    public function it_will_return_user_submitted_cards()
    {
        $gameService = new GameService();
        $user = User::factory()->create();
        $game = $gameService->createGame($user, Expansion::all()->pluck('id'));
        $submittedUser = User::factory()->create();
        $game->users()->attach($submittedUser);
        $game->users->each(fn($user) => $gameService->drawWhiteCards($user, $game));

        $gameService->submitCards($submittedUser->whiteCards->take(2)->pluck('id'), $game);

        $this->actingAs($submittedUser)
            ->getJson(route('api.game.submitted.cards', $game->id))
            ->assertOK()
            ->assertJsonStructure([
                'data' => [
                    [
                        'user_id',
                        'submitted_cards' => [
                             [
                                'id',
                                'text',
                                'expansion_id',
                                'order'
                            ]
                        ]
                    ]
                ]
            ]);
    }
}
