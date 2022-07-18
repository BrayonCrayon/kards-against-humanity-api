<?php

namespace Tests\Feature\Models\GameExpansion;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameExpansion;
use Tests\TestCase;

class GameExpansionTest extends TestCase
{
    /** @test */
    public function game_relationship_brings_back_game_type()
    {
        $gameExpansion = GameExpansion::factory()
            ->for(Expansion::factory())
            ->create();
        $this->assertInstanceOf(Game::class, $gameExpansion->game);
    }

    /** @test */
    public function expansion_relationship_brings_back_expansion_type()
    {
        $gameExpansion = GameExpansion::factory()
            ->for(Expansion::factory())
            ->create();
        $this->assertInstanceOf(Expansion::class, $gameExpansion->expansion);
    }
}
