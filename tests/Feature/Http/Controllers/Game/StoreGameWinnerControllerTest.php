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
