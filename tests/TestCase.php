<?php

namespace Tests;

use App\Models\BlackCard;
use App\Models\Game;
use App\Models\GameBlackCards;
use App\Models\RoundWinner;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;
    use WithFaker;
    public $gameService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gameService = new GameService();
    }

    public function drawBlackCardWithPickOf($pick, $game)
    {
        $drawnCards = $game->deletedBlackCards()->get();
        $pickedCard = BlackCard::whereIn('expansion_id', $game->expansions->pluck('id'))
            ->whereNotIn('id', $drawnCards->pluck('id'))
            ->where('pick', $pick)
            ->inRandomOrder()
            ->firstOrFail();

        $game->blackCards()->attach($pickedCard);

        return $pickedCard;
    }

    /**
     * @param $blackCardPick
     * @param $game
     */
    public function playersSubmitCards($blackCardPick, $game): void
    {
        $game->users->where('id', '<>', $game->judge->id)
            ->each(fn($user) => $this->gameService->submitCards($user->whiteCards->take($blackCardPick)->pluck('id'), $game, $user));
    }

    public function getNextJudge($user, $game): int
    {
        $this->playersSubmitCards($game->currentBlackCard->pick, $game);

        $this->actingAs($user)->postJson(route('api.game.rotate', $game->id))->assertOk();

        $game->refresh();

        $this->assertNotEquals($user->id, $game->judge->id);
        return $game->judge->id;
    }

    public function selectGameWinner(User $user, Game $game): void
    {
        $user->whiteCardsInGame()->whereSelected(true)->get()->each(function ($whiteGameCard) use ($user, $game) {
            RoundWinner::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'black_card_id' => $game->currentBlackCard->id,
                'white_card_id' => $whiteGameCard->white_card_id
            ]);
        });
    }
}
