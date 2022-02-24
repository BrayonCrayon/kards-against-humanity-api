<?php

namespace Tests\Feature\Http\Controllers\Game\Round;

use App\Models\Game;
use App\Services\GameService;
use Tests\TestCase;

class GetRoundWinnerControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->game = Game::factory()->hasUsers(1)->create();
        $this->user = $this->game->users->whereNotIn('id', [$this->game->judge->id])->first();

        $this->drawBlackCardWithPickOf(2, $this->game);
        $this->playersSubmitCards($this->game->currentBlackCard->pick, $this->game);
        $this->selectGameWinner($this->user, $this->game);
    }

    /** @test */
    public function it_will_call_game_service_to_retrieve_round_winner()
    {
        $serviceSpy = $this->spy(GameService::class);
        $serviceSpy->shouldReceive('latestRoundWinner')
            ->andReturn([
               'user' => $this->user,
               'userGameWhiteCards' => $this->user->whiteCardsInGame()->whereSelected(true)->get()
            ]);

        $this->actingAs($this->user)
            ->getJson(route('api.game.round.winner', [
                $this->game,
                $this->game->currentBlackCard
            ]))
            ->assertOk();

        $serviceSpy->shouldHaveReceived('latestRoundWinner')
            ->withArgs(function($game, $blackCard) {
                return $game->id === $this->game->id && $blackCard->id === $this->game->currentBlackCard->id;
            })
            ->once();
    }


    /** @test */
    public function it_returns_the_round_winner()
    {
        $this->actingAs($this->user)->getJson(route('api.game.round.winner', [$this->game->id, $this->game->currentBlackCard->id]))
            ->assertOK()
            ->assertJsonStructure([
                'data' => [
                    'user_id',
                    'submitted_cards' => [
                        [
                            'id',
                            'text',
                            'expansion_id',
                            'order',
                        ]
                    ]
                ]
            ]);

    }

    /** @test */
    public function it_returns_a_403_when_the_user_is_not_in_the_game()
    {
        $secondGame = Game::factory()->create();
        $this->actingAs($secondGame->judge)->getJson(route('api.game.round.winner', [$this->game->id, $this->game->currentBlackCard->id]))
            ->assertForbidden();
    }

    /** @test */
    public function it_returns_401_if_unauthorized()
    {
        $this->getJson(route('api.game.round.winner', [$this->game->id, $this->game->currentBlackCard->id]))
            ->assertUnauthorized();
    }


}
