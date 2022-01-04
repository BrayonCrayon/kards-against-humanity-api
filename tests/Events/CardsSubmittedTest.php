<?php

namespace Tests\Events;

use App\Events\CardsSubmitted;
use App\Models\Game;
use App\Models\User;
use Tests\TestCase;

class CardsSubmittedTest extends TestCase
{
    private $game;
    private $user;

    /** @test */
    public function it_sends_game_id_and_user_id() {

        $this->user = User::factory()->create();
        $this->game = Game::factory()->create([
            'judge_id' => $this->user->id,
        ]);
        $this->game->users()->save($this->user);

        $cardsSubmitted = new CardsSubmitted($this->game, $this->user);

        $payload = [
            'gameId' => $this->game->id,
            'userId' => $this->user->id,
        ];

        $this->assertEquals($payload, $cardsSubmitted->broadcastWith());
    }
}
