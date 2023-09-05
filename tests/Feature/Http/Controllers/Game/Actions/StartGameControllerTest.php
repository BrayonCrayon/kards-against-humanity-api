<?php

namespace Tests\Feature\Http\Controllers\Game\Actions;

use App\Events\RoundStart;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class StartGameControllerTest extends TestCase
{
    use GameUtilities;

    /** @test */
    public function it_starts_a_game()
    {
        Carbon::setTestNow(Carbon::now());
        $this->expectsEvents(RoundStart::class);
        $game = $this->createGame();
        $game->setting()->update([
            'selection_timer' => $this->faker->numberBetween(60,300)
        ]);

        $this->actingAs($game->judge)
            ->postJson(route('api.game.start', [$game->id]))
            ->assertSuccessful();

        $game->refresh();
        $this->assertEquals($game->selection_ends_at, Carbon::now()->unix() + $game->setting->selection_timer);
    }

    /** @test */
    public function it_will_reject_non_authed_users()
    {
        $game = $this->createGame();

        $this->postJson(route('api.game.start', $game))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_will_not_allow_user_to_start_other_games()
    {
        $game = $this->createGame();
        $user = User::factory()->create();

        $this->actingAs($user)
             ->postJson(route('api.game.start', $game))
             ->assertForbidden();

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'selection_ends_at' => null
        ]);
    }

    /** @test */
    public function it_will_only_allow_judge_to_start_game()
    {
        $game = $this->createGame(2);
        $user = $game->nonJudgeUsers()->first();

        $this->actingAs($user)
            ->postJson(route('api.game.start', $game))
            ->assertForbidden();
    }
}
