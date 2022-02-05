<?php

namespace Tests\Events;

use App\Events\WinnerSelected;
use App\Models\Game;
use App\Models\User;
use Tests\TestCase;

class WinnerSelectedTest extends TestCase
{
    /** @test */
    public function it_sends_user_id_and_game_id_in_event_payload()
    {
        $game = Game::factory()->create();
        $user = User::factory()->create();

        $event = new WinnerSelected($game, $user);

        $payload = [
            'game_id' => $game->id,
            'user_id' => $user->id
        ];

        $this->assertEquals($payload, $event->broadcastWith());
    }

    /** @test */
    public function it_broadcasts_on_correct_channel_name()
    {
        $game = Game::factory()->create();
        $user = User::factory()->create();

        $event = new WinnerSelected($game, $user);

        $this->assertEquals("game-{$game->id}", $event->broadcastOn()->name);
    }

    /** @test */
    public function it_broadcasts_on_correct_event_name()
    {
        $game = Game::factory()->create();
        $user = User::factory()->create();

        $event = new WinnerSelected($game, $user);

        $this->assertEquals("winner.selected", $event->broadcastAs());
    }
}
