<?php

namespace Tests\Feature\Actions;

use App\Actions\UserJoinsGame;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserJoinsGameTest extends TestCase
{
    /** @test */
    public function it_creates_a_new_user_if_the_user_belongs_to_another_game()
    {
        $game = Game::factory()->hasUsers(1)->create();
        $otherGame = Game::factory()->create();
        $player = $game->nonJudgeUsers()->first();

        $this->actingAs($player);

        $userJoinsGame = new UserJoinsGame(new GameService());
        $userJoinsGame($otherGame, $player->name);

        $this->assertNotEquals(auth()->user()->id, $player->id);
    }
}
