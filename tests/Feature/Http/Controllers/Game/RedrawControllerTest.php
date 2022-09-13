<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Game;
use App\Models\User;
use App\Models\UserGameWhiteCard;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class RedrawControllerTest extends TestCase
{
    use GameUtilities;

    /** @test */
    public function it_will_not_allow_non_authed_user_to_redraw()
    {
        $game = Game::factory()->create();

        $this->postJson(route('api.game.redraw', $game))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_will_put_players_hand_back_into_the_deck()
    {
        $game = $this->createGame();
        $user = $game->nonJudgeUsers()->first();

        $this->actingAs($user)
            ->postJson(route('api.game.redraw', $game))
            ->assertOK();

        $previousCards = UserGameWhiteCard::query()->onlyTrashed()->get();
        $this->assertCount(0, $previousCards);
    }

    /** @test */
    public function it_will_redraw_users_hand()
    {
        $game = $this->createGame();

        $hand = $game->judge->hand->pluck('id');

        $this->actingAs($game->judge)
            ->postJson(route('api.game.redraw', $game))
            ->assertOK()
            ->assertJsonCount(Game::HAND_LIMIT, 'data');

        $newHand = $game->judge->hand()->get()->pluck('id');
        $this->assertNotEquals($newHand, $hand);
        $this->assertDatabaseHas('game_users', [
            'user_id' => $game->judge->id,
            'redraw_count' => 1,
        ]);
    }

    /** @test */
    public function it_will_not_allow_the_user_redraw_past_their_limit()
    {
        $game = Game::factory()->create();
        $hand = $game->judge->hand->pluck('id');
        $user = $game->getPlayer($game->judge_id);
        $user->gameState->redraw_count = 2;
        $user->gameState->save();

        $this->actingAs($user)
            ->postJson(route('api.game.redraw', $game))
            ->assertForbidden();

        $newHand = $user->hand()->get()->pluck('id');
        $this->assertEquals($newHand, $hand);
        $this->assertDatabaseHas('game_users', [
            'user_id' => $user->id,
            'redraw_count' => 2
        ]);
    }
}
