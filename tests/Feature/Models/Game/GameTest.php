<?php

namespace Tests\Feature\Models\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use Tests\TestCase;

class GameTest extends TestCase
{

    /** @test */
    public function user_relationship_brings_back_user_type()
    {
        $game = Game::factory()
            ->hasUsers(1)
            ->create();
        $this->assertInstanceOf(User::class, $game->users->first());
    }

    /** @test */
    public function expansion_relationship_brings_back_expansion_type()
    {
        $game = Game::factory()
            ->hasAttached(Expansion::first())
            ->create();
        $this->assertInstanceOf(Expansion::class, $game->expansions->first());
    }
}
