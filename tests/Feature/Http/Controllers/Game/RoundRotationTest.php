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
        $gameService = new GameService();
        $this->expansionIds = [Expansion::first()->id];
        $users = User::factory(5)->create();
        $this->game = Game::factory()->create();
        foreach ($users as $user) {
            $gameService->grabWhiteCards($user, $this->game, $this->expansionIds);
            $this->game->users()->save($user);
        }
        $this->game->expansions()->saveMany(Expansion::idsIn($this->expansionIds)->get());
        $gameService->grabBlackCards($users->first(), $this->game, $this->expansionIds);
    }

    /** @test */
    public function rotating_gives_the_next_user_a_black_card()
    {
        $blackCardPick = $this->game->userGameBlackCards()->first()->blackCard->pick;
        $users = $this->game->users;
        $firstBlackCardUser = $this->game->getBlackCardUser();
        $users->map(function($user) use($blackCardPick) {
            $userCards = $user->whiteCardsInGame->take($blackCardPick);
            $this->actingAs($user)->postJson(route('api.game.submit', $this->game->id), [
                'whiteCardIds' => $userCards->pluck('white_card_id')->toArray(),
                'submitAmount' => $blackCardPick
            ])->assertOk();
        });

        $this->postJson(route('api.game.rotate', $this->game->id))->assertOk();
        $secondBlackCardUser = $this->game->getBlackCardUser();
        $this->assertNotEquals($firstBlackCardUser->id, $secondBlackCardUser->id);
    }
}
