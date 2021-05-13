<?php

namespace Tests\Unit\User;

use App\Models\Game;
use App\Models\User;
use tests\TestCase;

class UserRelationshipTest extends TestCase
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
