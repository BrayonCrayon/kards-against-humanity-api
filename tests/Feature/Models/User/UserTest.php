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
    private $user;
    private $game;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gameService = new GameService();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->game = $this->gameService->createGame($this->user, Expansion::all()->pluck('id'));

        $this->gameService->drawWhiteCards($this->user, $this->game);
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
        $this->gameService->submitCards($this->user->whiteCards->take(2)->pluck('id'), $this->game);
        $this->assertTrue($this->user->hasSubmittedWhiteCards);
    }

    /** @test */
    public function has_submitted_white_cards_attribute_returns_false_when_no_cards_are_submitted()
    {
        $this->assertFalse($this->user->hasSubmittedWhiteCards);
    }

    /** @test */
    public function it_returns_submitted_white_card_ids_when_user_has_submitted_cards()
    {
        $submittedWhiteCardIds = $this->user->whiteCards->take(2)->pluck('id');
        $this->gameService->submitCards($submittedWhiteCardIds, $this->game);
        $submittedWhiteCardIds->each(fn ($cardId) => $this->assertContains($cardId, $this->user->submittedWhiteCardIds));
    }

    /** @test */
    public function submitted_white_cards_returns_empty_array_when_there_are_no_submitted_cards()
    {
        $this->assertEmpty($this->user->submittedWhiteCardIds);
    }
}
