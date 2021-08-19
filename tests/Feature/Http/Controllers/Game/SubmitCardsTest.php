<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use App\Models\GameBlackCards;
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
    private $user;

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
        $users->each(function ($user) use ($gameService) {
            $gameService->grabWhiteCards($user, $this->game, $this->expansionIds);
        });

        $this->user = $this->game->users->last();
        $this->actingAs($this->user);
        $blackCard = BlackCard::where('pick', 2)->first();
        GameBlackCards::create([
            'game_id' => $this->game->id,
            'black_card_id' => $blackCard->id
        ]);
    }

    /** @test */
    public function user_cannot_submit_a_card_that_does_not_exit()
    {
        $invalid_card_id = 99999999;

        $this->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => [$invalid_card_id],
            'submitAmount' => 1
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function user_submits_cards_for_a_game()
    {
        $cards = $this->user->whiteCardsInGame->slice(0,2);
        $ids = $cards->pluck('white_card_id')->toArray();
        $blackCardPick = $this->game->gameBlackCards()->first()->blackCard->pick;

        $this->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => $ids,
            'submitAmount' => $blackCardPick
        ])->assertOk();

        foreach ($cards as $card) {
            $card->refresh();
            $this->assertTrue($card->selected);
        }
    }

    /** @test */
    public function user_cannot_submit_more_cards_than_the_black_card_pick()
    {
        $blackCardPick = $this->game->gameBlackCards()->first()->blackCard->pick;
        $ids = $this->user->whiteCardsInGame->pluck('white_card_id')->toArray();

        $this->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => $ids,
            'submitAmount' => $blackCardPick
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function user_cannot_submit_less_cards_than_the_black_card_pick()
    {
        $blackCardPick = $this->game->gameBlackCards()->first()->blackCard->pick;
        $ids = $this->user->whiteCardsInGame->pluck('white_card_id')->take($blackCardPick - 1);

        $this->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => $ids,
            'submitAmount' => $blackCardPick
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
