<?php

namespace Tests\Events;

use App\Events\WinnerSelected;
use App\Models\Game;
use App\Models\User;
use Tests\TestCase;

class WinnerSelectedTest extends TestCase
{
    private $game;
    private $user;
    private $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->game = Game::factory()->create();
        $this->user = User::factory()->create();

        $this->event = new WinnerSelected($this->game, $this->user);
    }

    /** @test */
    public function it_sends_user_id_and_game_id_in_event_payload()
    {
        $payload = [
            'game_id' => $this->game->id,
            'user_id' => $this->user->id
        ];

        $this->assertEquals($payload, $this->event->broadcastWith());
    }

    /** @test */
    public function it_broadcasts_on_correct_channel_name()
    {
        $this->assertEquals("game-{$this->game->id}", $this->event->broadcastOn()->name);
    }

    /** @test */
    public function it_broadcasts_on_correct_event_name()
    {
        $this->assertEquals("winner.selected", $this->event->broadcastAs());
    }
}
