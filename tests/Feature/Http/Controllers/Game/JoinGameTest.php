<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class JoinGameTest extends TestCase
{
    private $game;
    private $expansionIds;
    protected function setUp(): void
    {
        parent::setUp();
        $this->expansionIds = [Expansion::first()->id];
        $user = User::factory()->create();
        $this->game = Game::factory()->create();
        $this->game->users()->save($user);
        $this->game->expansions()->saveMany(Expansion::idsIn($this->expansionIds)->get());

        $gameService = new GameService();
        $gameService->grabBlackCards($user, $this->game, $this->expansionIds);
    }

    /** @test */
    public function it_adds_a_user_to_a_game()
    {
        $joinGameResponse = $this->postJson(route('api.game.join', $this->game->id), [
            'name' => $this->faker->userName
        ])->assertOK();
        $this->assertCount(1, GameUser::where('game_id', $this->game->id)->where('user_id', $joinGameResponse['user']['id'])->get());
    }

    /** @test */
    public function it_gives_a_user_white_cards_when_joining_a_game()
    {
        $joinGameResponse = $this->postJson(route('api.game.join', $this->game->id), [
            'name' => $this->faker->userName
        ])->assertOK();
        $this->assertCount(Game::HAND_LIMIT, User::find($joinGameResponse['user']['id'])->whiteCards);
    }

    /** @test */
    public function it_brings_back_white_cards_that_are_in_specific_expansions()
    {
        $joinGameResponse = $this->postJson(route('api.game.join', $this->game->id), [
            'name' => $this->faker->userName
        ])->assertOK();

        $currentExpansionIds = User::find($joinGameResponse['user']['id'])->whiteCards->pluck('expansion_id');

        // assert that the users white cards has an expansion id (pluck them out) - all of them are found in the expansion ids array
        $currentExpansionIds->each(function ($id) {
            $this->assertContains($id, $this->expansionIds);
        });
    }

    /** @test */
    public function it_validates_user_name_when_joining_a_game()
    {
        $joinGameResponse = $this->postJson(route('api.game.join', $this->game->id), [
            'name' => ''
        ])->assertJsonValidationErrors([
            'name'
        ]);
    }


}
