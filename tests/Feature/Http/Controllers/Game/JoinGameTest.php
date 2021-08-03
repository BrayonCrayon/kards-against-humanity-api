<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\GameUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class JoinGameTest extends TestCase
{
    private $game;
    protected function setUp(): void
    {
        parent::setUp();
        $expansionIds = Expansion::first()->pluck('id');
        $response = $this->postJson(route('api.game.store'), [
            'userName'   => $this->faker->userName,
            'expansionIds' => $expansionIds->toArray()
        ])->getOriginalContent();
        $this->game = $response['game'];
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

    }
}
