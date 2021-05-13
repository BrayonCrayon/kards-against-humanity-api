<?php

namespace Tests\Unit\GameUser;

use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use tests\TestCase;

class GameUserRelationshipTest extends TestCase
{

    /** @test */
    public function user_relationship_brings_back_user_type()
    {
        $gameUser = GameUser::factory()->create();
        $this->assertInstanceOf(User::class, $gameUser->user);
    }

    /** @test */
    public function game_relationship_brings_back_game_type()
    {
        $gameUser = GameUser::factory()->create();
        $this->assertInstanceOf(Game::class, $gameUser->game);
    }
}
