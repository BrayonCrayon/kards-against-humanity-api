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
    public function it_brings_back_user_instance_using_user_relationship()
    {
        $user = User::factory()->create();
        $gameWinner = GameWinner::create([
            'user_id' => $user->id,
            'game_id' => Game::factory()->create()->id,
            'white_card_id' => WhiteCard::first()->id,
            'black_card_id' => BlackCard::first()->id,
        ]);
        $this->assertInstanceOf(User::class, $gameWinner->user);
    }
}
