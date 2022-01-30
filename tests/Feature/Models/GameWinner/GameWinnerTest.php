<?php

namespace Tests\Feature\Models\GameWinner;

use App\Models\BlackCard;
use App\Models\Game;
use App\Models\GameWinner;
use App\Models\User;
use App\Models\WhiteCard;
use Tests\TestCase;

class GameWinnerTest extends TestCase
{
    /** @test */
    public function it_has_relationships()
    {
        $gameWinner = GameWinner::create([
            'user_id' => User::factory()->create()->id,
            'game_id' => Game::factory()->create()->id,
            'white_card_id' => WhiteCard::first()->id,
            'black_card_id' => BlackCard::first()->id,
        ]);

        $this->assertInstanceOf(User::class, $gameWinner->user);
        $this->assertInstanceOf(Game::class, $gameWinner->game);
        $this->assertInstanceOf(WhiteCard::class, $gameWinner->whiteCard);
        $this->assertInstanceOf(BlackCard::class, $gameWinner->blackCard);
    }
}
