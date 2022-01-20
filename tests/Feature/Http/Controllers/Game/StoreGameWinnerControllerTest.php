<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameWinner;
use App\Models\User;
use App\Services\GameService;
use Tests\TestCase;

class StoreGameWinnerControllerTest extends TestCase
{

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
        $player = $game->users->where('id', '<>',$game->judge->id)->first();

        $this->playersSubmitCards($game->currentBlackCard->pick, $game);

        $this->actingAs($game->judge)->postJson(route('api.game.winner', $game->id), [
           'user_id' => $player->id,
        ])->assertOk();

        $gameWinners = GameWinner::where('user_id', $player->id)->get();
        $this->assertCount($game->currentBlackCard->pick, $gameWinners);
    }
}
