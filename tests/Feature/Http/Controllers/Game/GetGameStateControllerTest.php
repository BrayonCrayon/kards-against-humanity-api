<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Events\GameJoined;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class GetGameStateControllerTest extends TestCase
{
    use GameUtilities;

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

        $game = $this->createGame(4);
        $user = $game->nonJudgeUsers()->first();

        $response = $this->actingAs($user)
            ->getJson(route('api.game.show', $game->id))
            ->assertOk();

        $response->assertJsonFragment([
            'id' => $game->id,
            'name' => $game->name,
            'code' => $game->code,
            'redrawLimit' => $game->redraw_limit,
            'judgeId' => $game->judge_id
        ]);

        $response->assertJsonFragment([
            'hasSubmittedWhiteCards' => false
        ]);

        $response->assertJsonFragment([
            'submittedWhiteCardIds' => []
        ]);

        $response->assertJsonFragment([
            'id' => $game->blackCard->id,
            'pick' => $game->blackCard->pick,
            'text' => $game->blackCard->text,
        ]);

        $game->users->each(function ($player) use ($response) {
            $response->assertJsonFragment([
                'id' => $player->id,
                'name' => $player->name,
                'score' => $player->score,
                'hasSubmittedWhiteCards' => $player->hasSubmittedWhiteCards
            ]);
        });

        $game->refresh();
        $this->assertCount(Game::HAND_LIMIT, $user->hand);
        $user->hand->each(function ($userCard) use ($response) {
            $response->assertJsonFragment([
                'id' => $userCard->whiteCard->id,
                'text' => $userCard->whiteCard->text,
                'expansionId' => $userCard->whiteCard->expansion_id,
                'order' => $userCard->order,
                'selected' => $userCard->selected
            ]);
        });

        $loggedInUser = $game->getPlayer($user->id);
        $response->assertJsonFragment([
            'currentUser' => [
                'id' => $loggedInUser->id,
                'name' => $loggedInUser->name,
                'score' => $loggedInUser->score,
                'hasSubmittedWhiteCards' => $loggedInUser->hasSubmittedWhiteCards,
                'isSpectator' => $loggedInUser->gameState->is_spectator,
                'redrawCount' => $loggedInUser->gameState->redraw_count
            ]
        ]);
    }
}
