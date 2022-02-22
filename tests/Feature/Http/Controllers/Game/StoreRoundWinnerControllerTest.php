<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Game;
use App\Models\RoundWinner;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class StoreRoundWinnerControllerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    /** @test */
    public function it_does_not_allow_game_that_does_not_exist()
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson(route('api.game.winner', $this->faker->uuid), [
            'user_id' => $user->id,
        ])->assertNotFound();
    }

    /** @test */
    public function it_does_not_allow_non_authed_user()
    {
        $game = Game::factory()->create();
        $this->postJson(route('api.game.winner', $game->id), [
            'user_id' => $game->judge->id,
        ])->assertUnauthorized();
    }

    /** @test */
    public function it_stores_game_winner_will_data()
    {
        $game = Game::factory()->hasUsers(1)->create();
        $player = $game->nonJudgeUsers()->first();

        $this->playersSubmitCards($game->blackCardPick, $game);

        $this->actingAs($game->judge)->postJson(route('api.game.winner', $game->id), [
           'user_id' => $player->id,
        ])->assertOk();

        $roundWinners = RoundWinner::where('user_id', $player->id)->get();
        $this->assertCount($game->currentBlackCard->pick, $roundWinners);
    }

    /** @test */
    public function it_calls_select_winner_from_game_service()
    {
        $game = Game::factory()->hasUsers(1)->create();
        $player = $game->nonJudgeUsers()->first();
        $this->playersSubmitCards($game->blackCardPick, $game);
        $gameServiceSpy = $this->spy(GameService::class);

        $this->actingAs($game->judge)
            ->postJson(route('api.game.winner', $game), [
                'user_id' => $player->id
            ])->assertOk();

        $gameServiceSpy->shouldHaveReceived('selectWinner')
            ->withArgs(function($gameArg, $userArg) use ($game, $player) {
                return $gameArg->id === $game->id && $userArg->id === $player->id;
            })
            ->once();
    }


}
