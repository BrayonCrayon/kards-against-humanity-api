<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class JoinGameTest extends TestCase
{
    private $game;
    protected function setUp(): void
    {
        parent::setUp();
        $expansionIds = Expansion::all()->pluck('id')->toArray();
        $user = User::factory()->create();
        $this->game = Game::factory()->create();
        $this->game->users()->save($user);
        $this->game->expansions()->saveMany(Expansion::idsIn($expansionIds)->get());
    }

    /** @test */
    public function it_adds_a_user_to_a_game()
    {
        $joinGameResponse = $this->postJson(route('api.game.join', $this->game->id), [
            'userName' => $this->faker->userName
        ])->assertOK();
        $this->assertCount(1, GameUser::where('game_id', $this->game->id)->where('user_id', $joinGameResponse['user']['id'])->get());
    }

    /** @test */
    public function it_gives_a_user_white_cards_when_joining_a_game()
    {
        $joinGameResponse = $this->postJson(route('api.game.join', $this->game->id), [
            'userName' => $this->faker->userName
        ])->assertOK();
        $this->assertCount(Game::HAND_LIMIT, User::find($joinGameResponse['user']['id'])->whiteCards);
    }
}
