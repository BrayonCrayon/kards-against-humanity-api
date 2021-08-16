<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use App\Models\UserGameBlackCards;
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
    public function rotating_changes_current_judge_to_new_user()
    {
        $this->withoutExceptionHandling();
        $blackCardPick = $this->game->userGameBlackCards()->first()->blackCard->pick;

        $users = $this->game->users;
        $firstJudge = $this->game->judge;
        $users->each(function($user) use($blackCardPick) {
            $userCards = $user->whiteCardsInGame->take($blackCardPick);
            $userCards->each(fn ($card) => $card->update(['selected' => true]));
        });

        $this->postJson(route('api.game.rotate', $this->game->id))->assertOk();

        $freshGame = Game::findOrFail($this->game->id);
        $secondJudge = $freshGame->judge;
        $this->assertNotEquals($firstJudge->id, $secondJudge->id);
    }
}
