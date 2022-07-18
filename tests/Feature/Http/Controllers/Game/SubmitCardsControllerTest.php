<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Events\CardsSubmitted;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class SubmitCardsControllerTest extends TestCase
{
    use GameUtilities;

    private $game;
    private User $user;

    private $route = 'api.game.select';

    protected function setUp(): void
    {
        parent::setUp();

        $this->game = $this->createGame(blackCardPick: 2);
        $this->user = $this->game->judge;
    }

    /** @test */
    public function user_cannot_select_a_card_that_does_not_exit()
    {
        $invalid_card_id = 99999999;

        $this->actingAs($this->user)->postJson(route($this->route, $this->game->id), [
            'whiteCardIds' => [$invalid_card_id],
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function user_submits_cards_for_a_game()
    {
        Event::fake();
        $cards = $this->user->hand->take(2);

        $this->actingAs($this->user)->postJson(route($this->route, $this->game->id), [
            'whiteCardIds' => $cards->pluck('white_card_id')->toArray(),
        ])->assertNoContent();

        $cards->each(function ($card) {
            $card->refresh();
            $this->assertTrue($card->selected);
        });
    }

    /** @test */
    public function user_cannot_submit_more_cards_than_the_black_card_pick()
    {
        $blackCardPick = $this->game->blackCard->pick;
        $ids = $this->user->hand->pluck('white_card_id')->toArray();

        $this->actingAs($this->user)->postJson(route($this->route, $this->game->id), [
            'whiteCardIds' => $ids,
            'submitAmount' => $blackCardPick
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function user_cannot_submit_less_cards_than_the_black_card_pick()
    {
        $blackCardPick = $this->game->blackCard->pick;
        $ids = $this->user->hand->pluck('white_card_id')->take($blackCardPick - 1);

        $this->actingAs($this->user)->postJson(route($this->route, $this->game->id), [
            'whiteCardIds' => $ids,
            'submitAmount' => $blackCardPick
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function event_fired_when_user_submits_cards_for_a_game()
    {
        Event::fake();
        $cards = $this->user->hand->take(2);

        $this->actingAs($this->user)->postJson(route($this->route, $this->game->id), [
            'whiteCardIds' => $cards->pluck('white_card_id')->toArray(),
            'submitAmount' => $this->game->blackCard->pick
        ])->assertNoContent();

       Event::assertDispatched(CardsSubmitted::class, function (CardsSubmitted $event) {
           return $event->game->id === $this->game->id
               && $event->broadcastOn()->name === 'game-' . $this->game->id
               && $this->user->id === $event->user->id;
       });
    }

    /** @test */
    public function user_submitting_cards_will_keep_the_order_they_were_submitted_in() {
        Event::fake();
        $cardsToSubmit = $this->user->hand->take(2);

        $this->actingAs($this->user)
            ->postJson(route($this->route, $this->game->id), [
                'whiteCardIds' => $cardsToSubmit->pluck('white_card_id')->toArray(),
                'submitAmount' => $this->game->blackCard->pick
            ])->assertNoContent();

        $orderNum = 1;
        $cardsToSubmit->each(function($submittedCard) use (&$orderNum) {
            $this->assertDatabaseHas('user_game_white_cards', [
                'id' => $submittedCard->id,
                'order' => $orderNum
            ]);
            ++$orderNum;
        });
    }
}
