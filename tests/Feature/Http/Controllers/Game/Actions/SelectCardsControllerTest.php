<?php

namespace Tests\Feature\Http\Controllers\Game\Actions;

use App\Events\RoundStart;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class SelectCardsControllerTest extends TestCase
{
    use GameUtilities;

    /** @test */
    public function it_will_start_the_judge_round_when_the_last_player_selects_their_cards()
    {
        $this->markTestSkipped('Pending player reaction to selection timer');
        Carbon::setTestNow();
        $game = $this->createGame(2);
        $game->setting->update([
            'selection_timer' => 60,
        ]);
        [$player1, $player2] = $game->nonJudgeUsers->all();
        $this->selectCardsForUser($player1, $game);
        $cardsToSelect = $player2->whiteCards->take($game->blackCardPick)->pluck('id');

        $this->expectsEvents(RoundStart::class);

        $this->actingAs($player2)
            ->postJson(route('api.game.select', $game), ['whiteCardIds' => $cardsToSelect])
            ->assertSuccessful();

        $this->assertEquals(now()->unix() + 60, $game->refresh()->selection_ends_at);
    }

    /** @test */
    public function it_will_only_start_a_new_round_when_last_player_selects_their_card()
    {
        $this->markTestSkipped('Pending player reaction to selection timer');
        $game = $this->createGame(2);
        $player1 = $game->users()->first();

        $cardsToSelect = $player1->whiteCards->take($game->blackCardPick)->pluck('id');

        $this->doesntExpectEvents(RoundStart::class);

        $this->actingAs($player1)
            ->postJson(route('api.game.select', $game), ['whiteCardIds' => $cardsToSelect])
            ->assertSuccessful();
    }

    /** @test */
    public function it_will_not_set_selection_ends_at_when_no_timer_settings_is_set()
    {
        $this->markTestSkipped('Pending player reaction to selection timer');
        $game = $this->createGame(2);
        [$player1, $player2] = $game->nonJudgeUsers->all();
        $this->selectCardsForUser($player1, $game);
        $cardsToSelect = $player2->whiteCards->take($game->blackCardPick)->pluck('id');

        $this->doesntExpectEvents(RoundStart::class);

        $this->actingAs($player2)
            ->postJson(route('api.game.select', $game), ['whiteCardIds' => $cardsToSelect])
            ->assertSuccessful();

        $this->assertEquals(null, $game->refresh()->selection_ends_at);
    }
}
