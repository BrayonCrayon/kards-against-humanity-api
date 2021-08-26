<?php

namespace Tests\Feature;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Models\UserGameWhiteCards;
use App\Models\WhiteCard;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GameServiceTest extends TestCase
{


    private GameService $gameService;

    private const REALLY_SMALL_EXPANSION_ID = 105;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gameService = new GameService();
    }

    /** @test */
    public function it_does_not_draw_white_cards_that_have_already_been_drawn()
    {
        $user = User::factory()->create();
        $game = Game::create([
            'name' => 'Krombopulos Michael',
            'judge_id' => $user->id,
        ]);
        $game->users()->save($user);
        $game->expansions()->saveMany(Expansion::idsIn([self::REALLY_SMALL_EXPANSION_ID])->get());

        $playedCards = WhiteCard::whereExpansionId(self::REALLY_SMALL_EXPANSION_ID)->limit(4)->get();
        $playedCards->each(function ($whiteCard) use ($user, $game) {
            UserGameWhiteCards::create([
                'white_card_id' => $whiteCard->id,
                'game_id' => $game->id,
                'user_id' => $user->id,
            ]);
        });
        $lastRemainingCard = WhiteCard::query()->whereExpansionId(self::REALLY_SMALL_EXPANSION_ID)->whereNotIn('id', $playedCards->pluck('id')->toArray())->firstOrFail();

        $pickedCards = $this->gameService->drawWhiteCards($user, $game, 2);

        $this->assertCount(1, $pickedCards);

        //assert card drawn is not in white card game user table
        $this->assertEquals($lastRemainingCard->id, $pickedCards->first()->id);
    }
}
