<?php

namespace Tests\Feature\Models\User;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Tests\TestCase;

class UserTest extends TestCase
{
    private $gameService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gameService = new GameService();
    }

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
        $user = User::factory()->create();
        $this->actingAs($user);
        $game = $this->gameService->createGame($user, Expansion::all()->pluck('id'));

        $this->gameService->drawWhiteCards($user, $game);
        $this->gameService->submitCards($user->whiteCards->take(2)->pluck('id'),$game);
        $this->assertTrue($user->hasSubmittedWhiteCards);
    }

    /** @test */
    public function has_submitted_white_cards_attribute_returns_false_when_no_cards_are_submitted()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $game = $this->gameService->createGame($user, Expansion::all()->pluck('id'));

        $this->gameService->drawWhiteCards($user, $game);
        $this->assertFalse($user->hasSubmittedWhiteCards);
    }

    /** @test */
    public function it_returns_submitted_white_card_ids_when_user_has_submitted_cards()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $game = $this->gameService->createGame($user, Expansion::all()->pluck('id'));

        $this->gameService->drawWhiteCards($user, $game);

        $submittedWhiteCardIds = $user->whiteCards->take(2)->pluck('id');
        $this->gameService->submitCards($submittedWhiteCardIds,$game);
        $submittedWhiteCardIds->each(fn ($cardId) => $this->assertContains($cardId, $user->submittedWhiteCardIds));
    }

    /** @test */
    public function submitted_white_cards_returns_empty_array_when_there_are_no_submitted_cards()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $game = $this->gameService->createGame($user, Expansion::all()->pluck('id'));

        $this->gameService->drawWhiteCards($user, $game);

        $this->assertEmpty($user->submittedWhiteCardIds);
    }
}
