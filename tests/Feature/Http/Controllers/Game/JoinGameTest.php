<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\GameUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class JoinGameTest extends TestCase
{
    /** @test */
    public function it_adds_a_user_to_a_game()
    {
        $expansionIds = Expansion::first()->pluck('id');
        $response = $this->postJson(route('api.game.store'), [
            'userName'   => $this->faker->userName,
            'expansionIds' => $expansionIds->toArray()
        ])->assertOk()
        ->getOriginalContent();

        $gameId = $response['game']->id;

        // new user joins the game
        $joinGameResponse = $this->postJson(route('api.game.join', $response['game']->id), [
            'userName' => $this->faker->userName
        ])->assertOK();

        $this->assertCount(1, GameUser::where('game_id', $gameId)->where('user_id', $joinGameResponse['user']->id)->count());
    }
}
