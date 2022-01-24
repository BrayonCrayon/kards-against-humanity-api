<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Game;
use App\Models\User;
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
        $game = Game::factory()->hasUsers(1)->create();
        $submittedUser = $game->users->whereNotIn('id', [$game->judge->id])->first();

        $this->playersSubmitCards($game->currentBlackCard->pick, $game);

        $response = $this->actingAs($submittedUser)
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
                                'order',
                                'selected',
                            ]
                        ]
                    ]
                ]
            ]);

        $this->assertCount(1, $response->json("data"));
        $this->assertCount($submittedUser->whiteCardsInGame()->selected()->count(), $response->json("data")[0]["submitted_cards"]);
        $submittedUser->whiteCardsInGame()->selected()->get()->each(function ($whiteCardInGame) use ($response) {
            $response->assertJsonFragment([
                'id' => $whiteCardInGame->white_card_id,
                'text' => $whiteCardInGame->whiteCard->text,
                'expansion_id' => $whiteCardInGame->whiteCard->expansion_id,
                'order' => $whiteCardInGame->order,
                'selected' => $whiteCardInGame->selected,
            ]);
        });
    }
}
