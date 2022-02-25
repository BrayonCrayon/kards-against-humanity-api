<?php

namespace Tests\Feature;

use App\Events\GameJoined;
use App\Events\WinnerSelected;
use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameBlackCards;
use App\Models\User;
use App\Models\UserGameWhiteCards;
use App\Models\WhiteCard;
use App\Services\HelperService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class GameServiceTest extends TestCase
{
    private const REALLY_SMALL_EXPANSION_ID = 105;
    private const REALLY_CHUNKY_EXPANSION_ID = 150;

    private $user;
    private $game;
    private $playedCards;
    private $helperService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helperService = new HelperService();
    }

    public function gameSetup($expansionId)
    {
        $this->user = User::factory()->create();
        $this->game = Game::create([
            'name' => 'Krombopulos Michael',
            'judge_id' => $this->user->id,
            'code' => $this->helperService->generateCode("?#?#")
        ]);
        $this->game->users()->saveMany(User::factory()->count(3)->create());
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

        $blackCards = BlackCard::query()->where("expansion_id", self::REALLY_SMALL_EXPANSION_ID)->get();

        $remainingCard = $blackCards->pop();

        $blackCards->each(fn($card) => GameBlackCards::create([
            'game_id' => $this->game->id,
            'black_card_id' => $card->id,
            'deleted_at' => now(),
        ]));

        $drawnCard = $this->gameService->drawBlackCard($this->game);

        $this->assertEquals($remainingCard->id, $drawnCard->id);
    }

    /** @test */
    public function it_emits_an_event_when_a_user_joins_a_game()
    {
        $this->gameSetup(self::REALLY_CHUNKY_EXPANSION_ID);
        Event::fake();

        /** @var User $user */
        $user = User::factory()->create();

        $this->gameService->joinGame($this->game, $user);

        Event::assertDispatched(GameJoined::class, function (GameJoined $event) use ($user) {
            return $event->game->id === $this->game->id && $user->id === $event->user->id;
        });
    }

    /** @test */
    public function it_emits_an_event_when_judge_user_selects_a_round_winner()
    {
        $this->gameSetup(self::REALLY_CHUNKY_EXPANSION_ID);
        Event::fake();

        $user = User::factory()->hasGames($this->game)->create();

        $this->gameService->selectWinner($this->game, $user);

        Event::assertDispatched(WinnerSelected::class, function (WinnerSelected $event) use ($user) {
            return $event->user->id === $user->id && $event->game->id === $this->game->id;
        });
    }

    /** @test */
    public function calling_get_submitted_card_brings_back_all_submitted_cards()
    {
        $this->gameSetup(self::REALLY_CHUNKY_EXPANSION_ID);
        $this->drawBlackCardWithPickOf(2, $this->game);
        $this->playersSubmitCards($this->game->blackCardPick, $this->game);

        $data = $this->gameService->getSubmittedCards($this->game);

        $this->assertEquals($this->game->nonJudgeUsers->count(), $data->count());

        $data->each(function ($item, $key) {
            $this->assertTrue($this->game->nonJudgeUsers->pluck("id")->contains($item["user_id"]));
        });

        $data->each(function ($item, $key) use ($data) {
            $selectedCards = UserGameWhiteCards::where("user_id", $item["user_id"])->where("selected", true)->get()->pluck("white_card_id");

            $item["submitted_cards"]->each(function ($item, $key) use ($selectedCards) {
                $this->assertTrue($selectedCards->contains($item->id));
            });
        });
    }

    /** @test */
    public function it_will_bring_back_latest_round_winner_data()
    {
        $game = Game::factory()->hasUsers(1)->create();
        $this->drawBlackCardWithPickOf(2, $game);
        $this->playersSubmitCards($game->blackCardPick, $game);

        $playerWinner = $game->nonJudgeUsers()->first();
        $this->selectGameWinner($playerWinner, $game);

        $winnerData = $this->gameService->roundWinner($game, $game->currentBlackCard);

        $selectedWhiteCardIds = $playerWinner->whiteCardsInGame()->whereSelected(true)->get()->pluck('id');
        $this->assertEquals($playerWinner->id, $winnerData['user']['id']);
        $this->assertCount($selectedWhiteCardIds->count(), $winnerData['userGameWhiteCards']);

        collect($winnerData['userGameWhiteCards'])->each(function ($whiteCard) use ($selectedWhiteCardIds) {
            $this->assertInstanceOf(UserGameWhiteCards::class, $whiteCard);
            $this->assertTrue($selectedWhiteCardIds->contains($whiteCard['id']));
        });
    }

    /** @test */
    public function it_will_bring_back_round_winner_data_from_a_previous_round()
    {
        $game = Game::factory()->hasUsers(1)->create();
        $this->drawBlackCardWithPickOf(2, $game);
        $this->playersSubmitCards($game->blackCardPick, $game);

        $playerWinner = $game->nonJudgeUsers()->first();
        $this->selectGameWinner($playerWinner, $game);
        $previousBlackCard = $game->currentBlackCard;
        $selectedWhiteCardCount = $playerWinner->whiteCardsInGame()->whereSelected(true)->count();

        $this->gameService->rotateGame($game);

        $winnerData = $this->gameService->roundWinner($game, $previousBlackCard);

        $this->assertEquals($playerWinner->id, $winnerData['user']['id']);
        $this->assertCount($selectedWhiteCardCount, $winnerData['userGameWhiteCards']);
    }

    /** @test */
    public function it_will_bring_back_correct_card_amount_for_each_user_after_game_rotate()
    {
        $game = Game::factory()->hasUsers(1)->create();
        $this->drawBlackCardWithPickOf(2, $game);
        $this->playersSubmitCards($game->blackCardPick, $game);

        $playerWinner = $game->nonJudgeUsers()->first();
        $this->selectGameWinner($playerWinner, $game);

        $this->gameService->rotateGame($game);

        $playerWinner = $playerWinner->refresh();
        $this->assertCount(Game::HAND_LIMIT, $playerWinner->whiteCardsInGame);
    }


}
