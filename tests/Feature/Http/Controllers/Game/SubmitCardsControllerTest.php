<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Events\CardsSubmitted;
use App\Models\Expansion;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SubmitCardsControllerTest extends TestCase
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

    /** @test */
    public function event_fired_when_user_submits_cards_for_a_game()
    {
        Event::fake();
        $this->gameService->discardBlackCard($this->game);
        $this->drawBlackCardWithPickOf(2, $this->game);
        $this->game->refresh();

        $cards = $this->user->whiteCardsInGame->take(2);

        $this->actingAs($this->user)->postJson(route('api.game.submit', $this->game->id), [
            'whiteCardIds' => $cards->pluck('white_card_id')->toArray(),
            'submitAmount' => $this->game->currentBlackCard->pick
        ])->assertNoContent();

       Event::assertDispatched(CardsSubmitted::class, function (CardsSubmitted $event) use ($cards) {
           $cardIds = collect($event->cards)->pluck('id');
           return $event->game->id === $this->game->id
               && $event->broadcastOn()->name === 'private-game.' . $this->game->id
               && count($event->cards) === $cards->count()
               && $this->user->id === $event->user->id
               && $cardIds->contains($cards->first()->white_card_id)
               && $cardIds->contains($cards->last()->white_card_id);
       });
    }
}
