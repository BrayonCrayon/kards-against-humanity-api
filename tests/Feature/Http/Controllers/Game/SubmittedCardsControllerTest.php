<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Hamcrest\Core\IsEqual;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class SubmittedCardsControllerTest extends TestCase
{
    use GameUtilities;

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
        $game = $this->createGame();

        $submittedUser = $game->nonJudgeUsers->first();

        $this->selectAllPlayersCards($game);

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
                                'expansionId',
                                'order'
                            ]
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_will_bring_back_correct_submitted_user_cards()
    {
        $game = $this->createGame();
        $submittedUser = $game->nonJudgeUsers->first();

        $this->selectAllPlayersCards($game);

        $response = $this->actingAs($submittedUser)
            ->getJson(route('api.game.submitted.cards', $game->id))
            ->assertOK();

        $this->assertCount(1, $response->json("data"));
        $this->assertCount($submittedUser->hand()->selected()->count(), $response->json("data")[0]["submitted_cards"]);
        $submittedUser->hand()->selected()->get()->each(function ($whiteCardInGame) use ($response) {
            $response->assertJsonFragment([
                'id' => $whiteCardInGame->white_card_id,
                'text' => $whiteCardInGame->whiteCard->text,
                'expansionId' => $whiteCardInGame->whiteCard->expansion_id,
                'order' => $whiteCardInGame->order
            ]);
        });
    }

    /** @test */
    public function call_get_submitted_cards_from_game_service_when_getting_submitted_cards()
    {
        $game = $this->createGame();
        $submittedUser = $game->nonJudgeUsers->first();

        $this->selectAllPlayersCards($game);

        $spy = $this->spy(GameService::class);

        $this->actingAs($submittedUser)
            ->getJson(route('api.game.submitted.cards', $game->id))
            ->assertOK();

        $spy->shouldHaveReceived("getSubmittedCards")
            ->withArgs(function($argument) use ($game) {
                return $argument->id === $game->id;
            })
            ->once();
    }
}
