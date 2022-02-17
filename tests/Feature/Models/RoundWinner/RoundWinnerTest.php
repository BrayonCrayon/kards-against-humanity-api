<?php

namespace Tests\Feature\Models\RoundWinner;

use App\Models\BlackCard;
use App\Models\Game;
use App\Models\RoundWinner;
use App\Models\User;
use App\Models\WhiteCard;
use Tests\TestCase;

class RoundWinnerTest extends TestCase
{
    /** @test */
    public function it_has_relationships()
    {
        $roundWinner = RoundWinner::create([
            'user_id' => User::factory()->create()->id,
            'game_id' => Game::factory()->create()->id,
            'white_card_id' => WhiteCard::first()->id,
            'black_card_id' => BlackCard::first()->id,
        ]);

        $this->assertInstanceOf(User::class, $roundWinner->user);
        $this->assertInstanceOf(Game::class, $roundWinner->game);
        $this->assertInstanceOf(WhiteCard::class, $roundWinner->whiteCard);
        $this->assertInstanceOf(BlackCard::class, $roundWinner->blackCard);
    }
}
