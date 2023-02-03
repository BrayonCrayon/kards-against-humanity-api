<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\GameUser;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class SpectateGameControllerTest extends TestCase
{
    use GameUtilities;

    private $game;

    protected function setUp(): void
    {
        parent::setUp();
        $this->game = $this->createGame();
    }

    /** @test */
    public function it_will_allow_a_user_to_spectate_a_game()
    {
        $this->postJson(route('api.game.spectate', $this->game->code))
            ->assertSuccessful();

        $this->assertCount(1, $this->game->spectators);
    }

    /** @test */
    public function it_will_return_game_data_to_spectator()
    {
        $blackCard = $this->game->blackCard;
        $response = $this->postJson(route('api.game.spectate', $this->game->code))
            ->assertSuccessful()
            ->assertJsonFragment([
                    'game' => [
                        'code' => $this->game->code,
                        'id' => $this->game->id,
                        'judgeId' => $this->game->judge_id,
                        'name' => $this->game->name,
                        'redrawLimit' => $this->game->redraw_limit,
                        'selectionEndsAt' => $this->game->selection_ends_at,
                        'selectionTimer' => $this->game->setting->selection_timer
                    ],
            ]);

        $this->game->users->each(function($user) use ($response) {
            $response->assertJsonFragment([
                'id' => $user->id,
                'name' => $user->name
            ]);
        });

        $spectateUser = GameUser::where('is_spectator', true)->where('game_id', $this->game->id)->first();

        $response->assertJsonFragment([
            'user' => [
                'id' => $spectateUser->user->id,
                'name' => $spectateUser->user->name,
                'isSpectator' => true,
                'redrawCount' => 0,
                'hasSubmittedWhiteCards' => $spectateUser->user->hasSubmittedWhiteCards,
                'score' => $spectateUser->user->score
            ]
        ]);
        $response->assertJsonFragment([
            'blackCard' => [
                'id' => $blackCard->id,
                'pick' => $blackCard->pick,
                'text' => $blackCard->text,
                'expansionId' => $blackCard->expansion_id,
            ]
        ]);
    }
}
