<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\UserGameWhiteCard;
use App\Services\GameService;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class KickPlayerControllerTest extends TestCase
{
    use GameUtilities;

    private $game;
    public $gameService;
    protected function setUp(): void
    {
        parent::setUp();
        $this->gameService = new GameService();
        $this->game = Game::factory()->hasUsers(2)->create();
    }

    /** @test */
    public function it_will_not_allow_non_auth_users_to_kick_players()
    {
        $player = $this->game->nonJudgeUsers()->first();
        $this->postJson(route('api.game.player.kick', [$this->game->id, $player->id]))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_will_not_allow_auth_user_to_kick_a_player_invalid_game()
    {
        $player = $this->game->nonJudgeUsers()->first();
        $this->actingAs($this->game->judge)
            ->postJson(route('api.game.player.kick', [$this->faker->uuid, $player->id]))
            ->assertNotFound();
    }

    /** @test */
    public function it_will_reject_invalid_player_ids()
    {
        $this->actingAs($this->game->judge)
            ->postJson(route('api.game.player.kick', [$this->game->id, 0]))
            ->assertNotFound();
    }

    /** @test */
    public function it_will_reject_non_judge_players_from_kicking_users()
    {
        $player = $this->game->nonJudgeUsers()->first();
        $this->actingAs($player)
            ->postJson(route('api.game.player.kick', [$this->game, $player]))
            ->assertForbidden();
    }

    /** @test */
    public function it_will_reject_judge_players_to_kick_other_players_of_another_game()
    {
        $differentGame = Game::factory()->create();
        $playerToKick = $this->game->nonJudgeUsers()->first();
        $this->actingAs($differentGame->judge)
            ->postJson(route('api.game.player.kick', [$this->game, $playerToKick]))
            ->assertForbidden();
    }

    /** @test */
    public function it_will_kick_player_from_game()
    {
        $game = Game::factory()
            ->has(Expansion::factory()->hasWhiteCards(21)->hasBlackCards(1))
            ->hasUsers(2)
            ->create();
        $this->drawBlackCard($game);
        $game->players()->each(fn ($user) => $this->gameService->drawWhiteCards($user, $game));
        $playerToKick = $game->nonJudgeUsers()->first();
        $playerCount = $game->users()->count();

        $this->actingAs($game->judge)
            ->postJson(route('api.game.player.kick', [$game, $playerToKick]))
            ->assertOK();
        $game->refresh();

        $this->assertCount($playerCount - 1, $game->users);
        $this->assertCount($game->players->count() * Game::HAND_LIMIT, UserGameWhiteCard::whereGameId($game->id)->get());
    }


}
