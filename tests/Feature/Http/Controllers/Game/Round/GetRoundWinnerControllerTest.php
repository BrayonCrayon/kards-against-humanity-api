<?php

namespace Tests\Feature\Http\Controllers\Game\Round;

use App\Models\Game;
use App\Models\RoundWinner;
use App\Models\User;
use Tests\TestCase;

class GetRoundWinnerControllerTest extends TestCase
{

    /** @test */
    public function it_returns_the_round_winner()
    {
        $game = Game::factory()->hasUsers(1)->create();
        $user = $game->users->whereNotIn('id', [$game->judge->id])->first();

        $this->drawBlackCardWithPickOf(2, $game);
        $this->playersSubmitCards($game->currentBlackCard->pick, $game);

        $user->whiteCardsInGame()->whereSelected(true)->get()->each(function ($whiteGameCard) use ($user, $game) {
            RoundWinner::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'black_card_id' => $game->currentBlackCard->id,
                'white_card_id' => $whiteGameCard->white_card_id
            ]);
        });

        $this->actingAs($user)->get(route('api.game.round.winner', $game->id))
            ->assertOK()
            ->assertJsonStructure([
                'user_id',
                'submitted_cards' => [
                    [
                        'id',
                        'text',
                        'expansion_id',
                       'order',
                    ]
                ]
            ]);

    }

    /** @test */
    public function it_returns_a_403_when_the_user_is_not_in_the_game()
    {
        $game = Game::factory()->hasUsers(1)->create();
        $user = $game->users->whereNotIn('id', [$game->judge->id])->first();

        $secondGame = Game::factory()->create();

        $this->drawBlackCardWithPickOf(2, $game);
        $this->playersSubmitCards($game->currentBlackCard->pick, $game);

        $user->whiteCardsInGame()->whereSelected(true)->get()->each(function ($whiteGameCard) use ($user, $game) {
            RoundWinner::create([
                'user_id' => $user->id,
                'game_id' => $game->id,
                'black_card_id' => $game->currentBlackCard->id,
                'white_card_id' => $whiteGameCard->white_card_id
            ]);
        });

        $this->actingAs($secondGame->judge)->get(route('api.game.round.winner', $game->id))
            ->assertForbidden();
    }
}
