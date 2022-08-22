<?php

namespace Tests\Feature\Http\Controllers\Game\Spectate;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class GetSpectateDataControllerTest extends TestCase
{

    use GameUtilities;

    /** @test */
    public function it_will_reject_non_auth_users()
    {
        $this->getJson(route('api.game.spectate.show', $this->faker->randomNumber()))
            ->assertUnauthorized();
    }

    /** @test */
    public function it_will_return_spectation_state()
    {
        $game = $this->createGame();
        $user = User::factory()->create();
        $game->users()->attach($user->id, ['is_spectator' => true]);

        $this->actingAs($user)
            ->getJson(route('api.game.spectate.show', $game))
            ->assertOk()
            ->assertJsonCount(4, 'data')
            ->assertJsonCount($game->players->count(), 'data.users')
            ->assertJsonFragment([
                'game' => [
                    'id' => $game->id,
                    'name' => $game->name,
                    'judgeId' => $game->judge_id,
                    'code' => $game->code,
                    'redrawLimit' => $game->redraw_limit
                ]
            ])->assertJsonFragment([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'redrawCount' => 0,
                    'isSpectator' => true,
                    'score' => 0,
                    'hasSubmittedWhiteCards' => false
                ]
            ])
            ->assertJsonFragment([
                'blackCard' => [
                    'id' => $game->blackCard->id,
                    'pick' => $game->blackCard->pick,
                    'text' => $game->blackCard->text,
                    'expansionId' => $game->blackCard->expansion_id,
                ]
            ]);
    }
}
