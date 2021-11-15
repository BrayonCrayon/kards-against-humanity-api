<?php

namespace Tests\Events;

use App\Events\GameJoined;
use App\Models\Game;
use App\Models\User;
use Tests\TestCase;

class GameJoinedTest extends TestCase
{
    private $game;
    private $user;

//    protected function setUp(): void
//    {
//        parent::setUp();
//
//    }

    /** @test */
    public function it_returns_game_and_user_information_from_broadcastWith()
    {
        $this->user = User::factory()->create();
        $this->game = Game::create([
            'name' => 'Krombopulos Michael',
            'judge_id' => $this->user->id,
            'code' => strval(random_int(0000, 9999))
        ]);
        $this->game->users()->save($this->user);


        $gameJoined = new GameJoined($this->game, $this->user);

        $payload = [
            'gameId' => $this->game->id,
            'userId' => $this->user->id,
        ];

        $this->assertEquals($payload, $gameJoined->broadcastWith());
    }
}
