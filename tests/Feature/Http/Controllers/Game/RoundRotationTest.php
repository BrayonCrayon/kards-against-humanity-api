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

class RoundRotationTest extends TestCase
{
    private $game;
    private $expansionIds;

    protected function setUp(): void
    {
        parent::setUp();
        $this->expansionIds = [Expansion::first()->id];
        $users = User::factory(5)->create();
        $this->game = Game::factory()->create();
        foreach ($users as $user) {
            $this->game->users()->save($user);
        }
        $this->game->expansions()->saveMany(Expansion::idsIn($this->expansionIds)->get());

        $gameService = new GameService();
        $gameService->grabBlackCards($users->first(), $this->game, $this->expansionIds);
    }

    /** @test */
    public function rotating_gives_a_new_user_a_black_card()
    {
        // users submit their cards
        $cards = $this->user->whiteCardsInGame->slice(0,2);
        $ids = $cards->pluck('white_card_id')->toArray();
        $blackCardPick = $this->game->userGameBlackCards()->first()->blackCard->pick;

        // TODO: Extract out submit functionality
        $this->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => $ids,
            'submitAmount' => $blackCardPick
        ])->assertOk();
        // idea: store if user is holding a black card | store current player
        // rotation of black cards
        $this->postJson(route('api.game.rotate', $this->game->id))->assertOk();
        // assert that the id of the user with the black card is different

    }
}
