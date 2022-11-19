<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class LeaveGameControllerTest extends TestCase
{
    use GameUtilities;

    /** @test */
    public function it_will_allow_user_to_leave_a_game()
    {
        $game = Game::factory()->hasBlackCards()->hasUsers()->create();
        $player = User::first();

        $this->actingAs($player)
            ->postJson(route('api.game.leave', $game->id))->assertOK();

        $this->assertDatabaseMissing('game_users', ['user_id'  => $player->id]);
    }

    /** @test */
    public function it_will_not_allow_non_auth_to_leave_a_game()
    {
        $game = Game::factory()->create();
        $this->postJson(route('api.game.leave', $game->id))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_will_switch_judge_when_judge_leaves()
    {
        $service = new GameService();
        $game = $this->createGame();
        $judge = $game->judge;
        $player = $service->nextJudge($game);

        $this->actingAs($judge)
            ->postJson(route('api.game.leave', $game->id))
            ->assertOK();

        $game->refresh();
        $this->assertNotEquals($judge->id, $game->judge->id);
        $this->assertEquals($player->id, $game->judge->id);
    }

    /** @test */
    public function it_will_remove_left_users_white_cards()
    {
        $game = $this->createGame();
        $user = $game->nonJudgeUsers()->first();
        $this->selectCardsForUser($user, $game);

        $this->actingAs($user)
            ->postJson(route('api.game.leave', $game->id))
            ->assertOk();

        $this->assertDatabaseMissing('user_game_white_cards', [
            'user_id' => $user->id,
            'game_id' => $game->id
        ]);
    }
}
