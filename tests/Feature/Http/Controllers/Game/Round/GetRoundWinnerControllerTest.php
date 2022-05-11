<?php

namespace Tests\Feature\Http\Controllers\Game\Round;

use App\Models\Game;
use App\Services\GameService;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class GetRoundWinnerControllerTest extends TestCase
{
    use GameUtilities;
    protected function setUp(): void
    {
        parent::setUp();
        $this->game = $this->createGame(blackCardCount: 10);
        $this->user = $this->game->nonJudgeUsers()->first();
        $this->selectAllPlayersCards($this->game);
        $this->submitPlayerForRoundWinner($this->user, $this->game);
    }

    /** @test */
    public function it_will_call_game_service_to_retrieve_round_winner()
    {
        $serviceSpy = $this->spy(GameService::class);
        $serviceSpy->shouldReceive('roundWinner')
            ->andReturn([
               'user' => $this->user,
               'userGameWhiteCards' => $this->user->hand()->whereSelected(true)->get()
            ]);

        $this->actingAs($this->user)
            ->getJson(route('api.game.round.winner', [
                $this->game,
                $this->game->blackCard
            ]))
            ->assertOk();

        $serviceSpy->shouldHaveReceived('roundWinner')
            ->withArgs(function($game, $blackCard) {
                return $game->id === $this->game->id && $blackCard->id === $this->game->blackCard->id;
            })
            ->once();
    }


    /** @test */
    public function it_returns_the_round_winner()
    {
        $this->actingAs($this->user)->getJson(route('api.game.round.winner', [$this->game->id, $this->game->blackCard->id]))
            ->assertOK()
            ->assertJsonStructure([
                'data' => [
                    'user_id',
                    'submitted_cards' => [
                        [
                            'id',
                            'text',
                            'expansionId',
                            'order',
                        ]
                    ],
                    'black_card' => [
                        'id',
                        'pick',
                        'text',
                        'expansionId'
                    ]
                ]
            ]);

    }

    /** @test */
    public function it_returns_a_403_when_the_user_is_not_in_the_game()
    {
        $secondGame = Game::factory()->create();
        $this->actingAs($secondGame->judge)->getJson(route('api.game.round.winner', [$this->game->id, $this->game->blackCard->id]))
            ->assertForbidden();
    }

    /** @test */
    public function it_returns_401_if_unauthorized()
    {
        $this->getJson(route('api.game.round.winner', [$this->game->id, $this->game->blackCard->id]))
            ->assertUnauthorized();
    }


}
