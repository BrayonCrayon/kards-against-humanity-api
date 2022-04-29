<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Events\GameJoined;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GetGameStateControllerTest extends TestCase
{

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
        $game = Game::factory()->hasUsers(4)->create();

        $response = $this->actingAs($game->judge)
            ->getJson(route('api.game.show', $game->id))
            ->assertOk();

        $response->assertJsonFragment([
            'id' => $game->id,
            'name' => $game->name,
            'code' => $game->code,
            'redrawLimit' => $game->redraw_limit,
            'judge' => [
                'id' => $game->judge_id,
                'name' => $game->judge->name,
                'score' => $game->judge->score,
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

        $game->users->each(function ($player) use ($response) {
            $response->assertJsonFragment([
                'id' => $player->id,
                'name' => $player->name,
                'score' => $player->score,
                'has_submitted_white_cards' => $player->hasSubmittedWhiteCards
            ]);
        });

        $game->judge->whiteCardsInGame->each(function ($userCard) use ($response) {
            $response->assertJsonFragment([
                'id' => $userCard->whiteCard->id,
                'text' => $userCard->whiteCard->text,
                'expansion_id' => $userCard->whiteCard->expansion_id,
                'order' => $userCard->order,
                'selected' => $userCard->selected
            ]);
        });

        $loggedInUser = $game->getUser($game->judge_id);
        $response->assertJsonFragment([
            'id' => $loggedInUser->id,
            'name' => $loggedInUser->name,
            'score' => $loggedInUser->score,
            'has_submitted_white_cards' => $loggedInUser->hasSubmittedWhiteCards,
            'redrawCount' => $loggedInUser->gameState->redraw_count
        ]);
    }
}
