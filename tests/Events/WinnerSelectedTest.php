<?php

namespace Tests\Events;

use App\Events\WinnerSelected;
use App\Models\Game;
use Tests\TestCase;

class WinnerSelectedTest extends TestCase
{
    /** @test */
    public function it_sends_user_id_and_game_id_in_event_payload()
    {
        $game = Game::factory()->create();
        $userId = $this->faker->uuid;

        $winnerSelectedEvent = new WinnerSelected($game, $userId);

        $payload = [
            'game_id' => $game->id,
            'user_id' => $userId
        ];

        $this->assertEquals($payload, $winnerSelectedEvent->broadcastWith());
    }

}
