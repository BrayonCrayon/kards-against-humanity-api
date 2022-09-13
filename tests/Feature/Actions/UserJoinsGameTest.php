<?php

namespace Tests\Feature\Actions;

use App\Actions\CreatingUser;
use App\Actions\UserJoinsGame;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class UserJoinsGameTest extends TestCase
{
    use GameUtilities;

    /** @test */
    public function it_creates_a_new_user_if_the_user_belongs_to_another_game()
    {
        Event::fake();
        $game = Game::factory()->hasUsers(1)->create();
        $otherGame = Game::factory()->create();
        $player = $game->nonJudgeUsers()->first();

        $this->actingAs($player);

        $userJoinsGame = new UserJoinsGame(new GameService(), new CreatingUser());
        $userJoinsGame($otherGame, $player->name);

        $this->assertNotEquals(auth()->user()->id, $player->id);
    }

    /** @test */
    public function it_will_allow_an_existing_user_to_join_a_different_game()
    {
        Event::fake();
        $game = $this->createGame();
        $user = User::factory()->create();

        $this->actingAs($user);
        (new UserJoinsGame(new GameService(), new CreatingUser()))($game, $user);

        $game->refresh();
        $this->assertEquals($user->id, $game->players()->whereUserId($user->id)->first()->id);
    }
}
