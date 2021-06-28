<?php

namespace Tests\Feature\Models\User;

use App\Models\Game;
use App\Models\User;
use Tests\TestCase;

class UserTest extends TestCase
{

    /** @test */
    public function game_relationship_brings_back_game_type()
    {
        $user = User::factory()
            ->hasGames(1)
            ->create();
        $this->assertInstanceOf(Game::class, $user->games->first());
    }
}
