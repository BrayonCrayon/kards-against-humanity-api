<?php

namespace Tests\Feature;

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameBlackCards;
use App\Models\User;
use App\Models\UserGameWhiteCards;
use App\Models\WhiteCard;
use App\Services\GameService;
use Tests\TestCase;

class GameServiceTest extends TestCase
{
    private GameService $gameService;

    private const REALLY_SMALL_EXPANSION_ID = 105;
    private const REALLY_CHUNKY_EXPANSION_ID = 150;

    private $user;
    private $game;
    private $playedCards;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gameService = new GameService();
    }

    public function gameSetup($expansionId)
    {
        $this->user = User::factory()->create();
        $this->game = Game::create([
            'name' => 'Krombopulos Michael',
            'judge_id' => $this->user->id,
        ]);
        $this->game->users()->save($this->user);
        $this->game->expansions()->saveMany(Expansion::idsIn([$expansionId])->get());

        $this->playedCards = WhiteCard::whereExpansionId($expansionId)->limit(4)->get();
        $this->playedCards->each(function ($whiteCard) {
            UserGameWhiteCards::create([
                'white_card_id' => $whiteCard->id,
                'game_id' => $this->game->id,
                'user_id' => $this->user->id,
            ]);
        });
    }

    /** @test */
    public function it_does_not_draw_white_cards_that_have_already_been_drawn()
    {
        $this->gameSetup(self::REALLY_SMALL_EXPANSION_ID);
        $lastRemainingCard = WhiteCard::query()->whereExpansionId(self::REALLY_SMALL_EXPANSION_ID)->whereNotIn('id', $this->playedCards->pluck('id')->toArray())->firstOrFail();

        $pickedCards = $this->gameService->drawWhiteCards($this->user, $this->game);

        $this->assertCount(1, $pickedCards);

        $this->assertEquals($lastRemainingCard->id, $pickedCards->first()->id);
    }

    /** @test */
    public function it_calculates_the_number_of_cards_to_draw_based_on_how_many_cards_are_in_the_users_hand()
    {
        $this->gameSetup(self::REALLY_CHUNKY_EXPANSION_ID);

        $drawnCards = $this->gameService->drawWhiteCards($this->user, $this->game);

        $this->assertCount(3, $drawnCards);
    }

    /** @test */
    public function it_only_draws_available_black_cards()
    {
        $this->gameSetup(self::REALLY_SMALL_EXPANSION_ID);

        // get all black cards for a given expansion
        $blackCards = BlackCard::query()->where("expansion_id", self::REALLY_SMALL_EXPANSION_ID)->get();

        $remainingCard = $blackCards->pop();

        // insert the first 4 into the database
        $blackCards->each(fn($card) => GameBlackCards::create([
            'game_id' => $this->game->id,
            'black_card_id' => $card->id,
            'deleted_at' => now(),
        ]));

        // draw another black card (last one)
        $drawnCard = $this->gameService->drawBlackCard($this->game);

        // assert that the last black card is from the drawnCard variable
        $this->assertEquals($remainingCard->id, $drawnCard->id);
    }
}
