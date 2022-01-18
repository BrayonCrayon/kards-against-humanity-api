<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreGameWinnerControllerTest extends TestCase
{

    /** @test */
    public function it_stores_game_winner_will_data()
    {
        $gameService = new GameService();
        $judgeUser = User::factory()->create();
        $game = $gameService->createGame($judgeUser, Expansion::all()->pluck('id'));
        $player = User::factory()->create();
        $game->users()->attach($player);
        $game->users->each(fn($user) => $gameService->drawWhiteCards($user, $game));

        $selectedCard = $player->whiteCards->take(1)->pluck('id');

        $this->actingAs($player);
        $gameService->submitCards($selectedCard, $game);
        $this->actingAs($judgeUser);


        $this->postJson(route('api.game.winner', $game->id), [
           'user_id' => $player->id,
        ])->assertOk();

        $this->assertDatabaseHas('game_winners', [
            'user_id' => $player->id,
            'game_id' => $game->id,
            'white_card_id' => $selectedCard->first(),
            'black_card_id' => $game->currentBlackCard->id
        ]);
    }
}
