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
        $drawnCards = $game->gameBlackCards()->onlyTrashed()->get();
        $pickedCard = BlackCard::whereIn('expansion_id', $game->expansions->pluck('id'))
            ->whereNotIn('id', $drawnCards->pluck('black_card_id'))
            ->where('pick', $pick)
            ->inRandomOrder()
            ->firstOrFail();
        return GameBlackCards::create([
            'game_id' => $game->id,
            'black_card_id' => $pickedCard->id
        ]);
    }
}
