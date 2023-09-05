<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\User;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class UpdateGameSettingsControllerTest extends TestCase
{

    use GameUtilities;

    /** @test */
    public function it_will_update_game_settings()
    {
        $game = $this->createGame();
        $user = $game->users()->first();

        $payload = [
            'selection_timer' => $this->faker->numberBetween(60, 300),
        ];

        $this
            ->actingAs($user)
            ->postJson(route('api.game.settings.update', $game), $payload)
            ->assertOk();

        $this->assertEquals($payload['selection_timer'], $game->fresh()->setting->selection_timer);
    }

    /** @test */
    public function it_will_only_users_in_the_game_to_update_game_settings()
    {
        $game = $this->createGame();
        $guest = User::factory()->create();

        $originalSettings = $game->setting->toArray();

        $payload = [
            'selection_timer' => $this->faker->randomNumber(3) + $game->setting->selection_timer,
        ];

        $this
            ->actingAs($guest)
            ->postJson(route('api.game.settings.update', $game), $payload)
            ->assertForbidden();

        $game->refresh();

        $this->assertEquals($originalSettings['selection_timer'], $game->setting->selection_timer);
    }

    public function payloads() : array
    {
        return [
            [[ 'selection_timer' => 'taco']],
            [[ 'selection_timer' => 301]],
            [[ 'selection_timer' => 59]]
        ];
    }

    /**
     * @test
     * @dataProvider payloads
     */
    public function it_will_not_allow_strings_for_timers($payload)
    {
        $game = $this->createGame();
        $user = $game->users()->first();
        $originalSettings = $game->setting->toArray();

        $this
            ->actingAs($user)
            ->postJson(route('api.game.settings.update', $game), $payload)
            ->assertUnprocessable();

        $game->refresh();
        $this->assertEquals($originalSettings['selection_timer'], $game->setting->selection_timer);
    }
}
