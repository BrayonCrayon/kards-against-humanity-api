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
    public function taco()
    {
        $this->user = User::factory()->create();
        $this->game = Game::create([
            'name' => 'Krombopulos Michael',
            'judge_id' => $this->user->id,
            'code' => strval(random_int(0000, 9999))
        ]);
        $this->game->users()->save($this->user);


        $gameJoined = new GameJoined($this->game, $this->user);

        $this->assertEquals(['gameId' => $this->game->id], $gameJoined->broadcastWith());
    }
}
