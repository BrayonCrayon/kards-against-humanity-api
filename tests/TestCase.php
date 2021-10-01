<?php

namespace Tests;

use App\Models\BlackCard;
use App\Models\GameBlackCards;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use DatabaseTransactions;
    use WithFaker;

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
    public function usersSelectCards($blackCardPick, $game): void
    {
        $game->users->each(function ($user) use ($blackCardPick) {
            $userCards = $user->whiteCardsInGame->take($blackCardPick);
            $userCards->each(fn($card) => $card->update(['selected' => true]));
        });
    }
}
