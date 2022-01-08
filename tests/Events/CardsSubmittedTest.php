<?php

namespace Tests\Events;

use App\Events\CardsSubmitted;
use App\Models\Game;
use App\Models\User;
use Tests\TestCase;

class CardsSubmittedTest extends TestCase
{
    /** @test */
    public function it_sends_game_id_and_user_id() {

        $game = Game::factory()->create();

        $cardsSubmitted = new CardsSubmitted($game, $game->judge);

        $payload = [
            'gameId' => $game->id,
            'userId' => $game->judge->id,
        ];

        $this->assertEquals($payload, $cardsSubmitted->broadcastWith());
    }

    /** @test */
    public function it_broadcasts_on_correct_channel_name() {
        $game = Game::factory()->create();

        $cardsSubmitted = new CardsSubmitted($game, $game->judge);

        $this->assertEquals("cards.submitted", $cardsSubmitted->broadcastAs());
    }
}
