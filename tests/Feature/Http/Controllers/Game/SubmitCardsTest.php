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
    private User $user;
    private GameService $gameService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gameService = new GameService();

        $this->user = User::factory()->create();
        $this->game = $this->gameService->createGame($this->user, [Expansion::first()->id]);
        $this->game->users()->attach(User::factory(4)->create());
        $this->game->users->each(fn ($user) => $this->gameService->drawWhiteCards($user, $this->game));
        $this->gameService->drawWhiteCards($this->user, $this->game);
    }

    /** @test */
    public function user_cannot_submit_a_card_that_does_not_exit()
    {
        $invalid_card_id = 99999999;

        $this->actingAs($this->user)->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => [$invalid_card_id],
            'submitAmount' => 1
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function user_submits_cards_for_a_game()
    {
        $this->gameService->discardBlackCard($this->game);
        $this->drawBlackCardWithPickOf(2, $this->game);
        $this->game->refresh();

        $cards = $this->user->whiteCardsInGame->take(2);

        $this->actingAs($this->user)->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => $cards->pluck('white_card_id')->toArray(),
            'submitAmount' => $this->game->currentBlackCard->pick
        ])->assertNoContent();

        $cards->each(function ($card) {
            $card->refresh();
            $this->assertTrue($card->selected);
        });
    }

    /** @test */
    public function user_cannot_submit_more_cards_than_the_black_card_pick()
    {
        $blackCardPick = $this->game->currentBlackCard->pick;
        $ids = $this->user->whiteCardsInGame->pluck('white_card_id')->toArray();

        $this->actingAs($this->user)->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => $ids,
            'submitAmount' => $blackCardPick
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function user_cannot_submit_less_cards_than_the_black_card_pick()
    {
        $this->gameService->discardBlackCard($this->game);
        $this->drawBlackCardWithPickOf(2, $this->game);
        $this->game->refresh();

        $blackCardPick = $this->game->currentBlackCard->pick;
        $ids = $this->user->whiteCardsInGame->pluck('white_card_id')->take($blackCardPick - 1);

        $this->actingAs($this->user)->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => $ids,
            'submitAmount' => $blackCardPick
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
