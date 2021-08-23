<?php

namespace Tests\Feature\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Models\UserGameWhiteCards;
use App\Models\WhiteCard;
use App\Services\GameService;
use Illuminate\Http\Response;
use Tests\TestCase;

class DrawWhiteCardsTest extends TestCase
{
    /** @test */
    public function user_can_draw_more_white_cards()
    {
        $game = Game::factory()->has(User::factory()->count(1))->create();
        $gameService = new GameService();
        $user = User::first();
        $gameService->drawWhiteCards($user, $game);

        // delete some of there cards
        //        $user->whiteCardsInGame()->where('game', $game)-first

        // hit endpoint to draw more

        // assert full hand
    }
}
