<?php

namespace Tests\Feature\Models\User;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
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

    /** @test */
    public function has_submitted_white_cards_attribute_brings_back_true_if_user_has_submitted_cards()
    {
        $gameService = new GameService();
        $user = User::factory()->create();
        $this->actingAs($user);
        $game = $gameService->createGame($user, Expansion::all()->pluck('id'));

        $gameService->drawWhiteCards($user, $game);
        $gameService->submitCards($user->whiteCards->take(2)->pluck('id'),$game);
        $this->assertTrue($user->hasSubmittedWhiteCards);
    }

    /** @test */
    public function has_submitted_white_cards_attribute_returns_false_when_no_cards_are_submitted()
    {
        $gameService = new GameService();
        $user = User::factory()->create();
        $this->actingAs($user);
        $game = $gameService->createGame($user, Expansion::all()->pluck('id'));

        $gameService->drawWhiteCards($user, $game);
        $this->assertFalse($user->hasSubmittedWhiteCards);
    }
}
