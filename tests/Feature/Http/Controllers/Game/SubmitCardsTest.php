<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use App\Models\UserGameWhiteCards;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class SubmitCardsTest extends TestCase
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
        $users->each(function ($user) use ($gameService) {
            $gameService->grabWhiteCards($user, $this->game, $this->expansionIds);
        });
    }

    /** @test */
    public function user_submits_a_card_for_a_game()
    {
        $user = $this->game->users->last();
        $this->actingAs($user);

        $selectedCard = $user->whiteCardsInGame->first();

        $this->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => [$selectedCard->white_card_id]
        ])->assertOK();

        $selectedCard->refresh();
        $this->assertTrue($selectedCard->selected);
    }

    /** @test */
    public function user_cannot_submit_a_card_that_does_not_exit()
    {
        $user = $this->game->users->last();
        $this->actingAs($user);

        $invalid_card_id = 99999999;

        $this->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => [$invalid_card_id]
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function user_can_submit_2_cards()
    {
        $user = $this->game->users->last();
        $this->actingAs($user);

        $cards = $user->whiteCardsInGame->slice(0,2);
        $ids = $cards->pluck('white_card_id')->toArray();

        $this->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => $ids
        ])->assertOk();

        foreach ($cards as $card) {
            $card->refresh();
            $this->assertTrue($card->selected);
        }
    }
}
