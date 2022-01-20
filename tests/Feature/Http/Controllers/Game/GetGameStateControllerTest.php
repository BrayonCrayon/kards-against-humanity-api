<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Events\GameJoined;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GetGameStateControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_does_not_allow_non_auth_users()
    {
        $game = Game::factory()->create();
        $this->getJson(route('api.game.show', $game->id))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_does_not_accept_non_existing_id()
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->getJson(route('api.game.show', $this->faker->uuid))
            ->assertNotFound();
    }

    /** @test */
    public function it_returns_current_game_state()
    {
        Event::fake([GameJoined::class]);
        $players = User::factory()->count(4)->create();
        /** @var Game */
        $game = $this->gameService->createGame($players->first(), [Expansion::first()->id]);

        $players->filter(fn($player) => $player->id !== $players->first()->id)
            ->each(fn($player) => $this->gameService->joinGame($game, $player));


        $response = $this->actingAs($players->first())
            ->getJson(route('api.game.show', $game->id))
            ->assertOk();

        $response->assertJsonFragment([
                'id' => $game->id,
                'name' => $game->name,
                'judge' => [
                    'id' => $game->judge_id,
                    'name' => $game->judge->name,
                    'has_submitted_white_cards' => $game->judge->hasSubmittedWhiteCards,
                    'created_at' => $game->judge->created_at,
                    'updated_at' => $game->judge->updated_at,
                ],
        ]);

        $response->assertJsonFragment([
            'hasSubmittedWhiteCards' => false
        ]);

        $response->assertJsonFragment([
            'submittedWhiteCardIds' => []
        ]);

        $response->assertJsonFragment([
            'id' => $game->currentBlackCard->id,
            'pick' => $game->currentBlackCard->pick,
            'text' => $game->currentBlackCard->text,
        ]);

        $players->each(function ($player) use ($response) {
            $response->assertJsonFragment([
               'id' => $player->id,
               'name' => $player->name,
                'has_submitted_white_cards' => $player->hasSubmittedWhiteCards
            ]);
        });

        $players->first()->whiteCards->each(function ($whiteCard) use ($response) {
            $response->assertJsonFragment([
               'id' => $whiteCard->id,
               'text' => $whiteCard->text,
               'order' => $whiteCard->order,
               'expansion_id' => $whiteCard->expansion_id
            ]);
        });
    }
}
