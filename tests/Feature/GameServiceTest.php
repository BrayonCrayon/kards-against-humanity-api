<?php

namespace Tests\Feature;

use App\Events\GameJoined;
use App\Events\WinnerSelected;
use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Models\UserGameWhiteCard;
use App\Services\GameService;
use App\Services\HelperService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class GameServiceTest extends TestCase
{
    use GameUtilities;

    private const REALLY_SMALL_EXPANSION_ID = 105;
    private const REALLY_CHUNKY_EXPANSION_ID = 150;

    private $user;
    private $game;
    private $playedCards;
    private $helperService;
    public $gameService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->helperService = new HelperService();
        $this->gameService = new GameService();
    }

    /** @test */
    public function it_does_not_draw_white_cards_that_have_already_been_drawn()
    {
        $game = Game::factory()
            ->has(Expansion::factory()->hasWhiteCards(8)->hasBlackCards(1))
            ->hasUsers()
            ->create();
        $user = $game->nonJudgeUsers()->first();
        $this->drawCardsForUser($user, $game);
        $user->refresh();
        $lastRemainingCard = $game->expansions()
            ->first()
            ->whiteCards()
            ->whereNotIn('id', $user->hand->pluck('white_card_id'))
            ->first();
        $user->hand->firstOrFail()->delete();

        $pickedCards = $this->gameService->drawWhiteCards($user, $game);

        $this->assertCount(1, $pickedCards);
        $this->assertEquals($lastRemainingCard->id, $pickedCards->first()->id);
    }

    /** @test */
    public function it_calculates_the_number_of_cards_to_draw_based_on_how_many_cards_are_in_the_users_hand()
    {
        $game = Game::factory()
            ->has(Expansion::factory()->hasWhiteCards(10)->hasBlackCards(1))
            ->hasUsers()
            ->create();
        $user = $game->judge;
        $this->drawCardsForUser($user, $game);

        $user->hand()->limit(4)->delete();

        $drawnCards = $this->gameService->drawWhiteCards($user, $game);

        $this->assertCount(3, $drawnCards);
    }

    /** @test */
    public function it_only_draws_available_black_cards()
    {
        $game = $this->createGame();
        $this->drawBlackCard($game);
        $game->gameBlackCards()->delete();
        $remainingCard = BlackCard::factory()->create(['expansion_id' => $game->expansions->first()->id]);

        $drawnCard = $this->gameService->drawBlackCard($game);

        $this->assertEquals($remainingCard->id, $drawnCard->id);
    }

    /** @test */
    public function it_emits_an_event_when_a_user_joins_a_game()
    {
        $game = $this->createGame();
        Event::fake();

        /** @var User $user */
        $user = User::factory()->create();

        $this->gameService->joinGame($game, $user);

        Event::assertDispatched(GameJoined::class, function (GameJoined $event) use ($user, $game) {
            return $event->game->id === $game->id && $user->id === $event->user->id;
        });
    }

    /** @test */
    public function it_emits_an_event_when_judge_user_selects_a_round_winner()
    {
        Event::fake();
        $game = $this->createGame();
        $user = $game->nonJudgeUsers()->first();

        $this->gameService->selectWinner($game, $user);

        Event::assertDispatched(WinnerSelected::class, function (WinnerSelected $event) use ($user, $game) {
            return $event->user->id === $user->id && $event->game->id === $game->id;
        });
    }

    /** @test */
    public function calling_get_submitted_card_brings_back_all_submitted_cards()
    {
        $game = Game::factory()
            ->has(Expansion::factory()->hasWhiteCards(7)->has(BlackCard::factory()->pickOf2()))
            ->hasUsers()
            ->create();
        $user = $game->nonJudgeUsers()->first();
        $this->drawBlackCard($game);
        $this->drawCardsForUser($user, $game, 7);
        $this->selectCardsForUser($user, $game);
        $whiteCardIds = $user->hand()->whereSelected(true)->pluck('white_card_id');

        $data = $this->gameService->getSubmittedCards($game);
        $submittedData = $data->first();

        $this->assertCount(1, $data);
        $this->assertEquals($user->id, $submittedData["user_id"]);
        $this->assertCount(2, $submittedData["submitted_cards"]);
        $submittedData["submitted_cards"]->each(function ($item) use ($user, $whiteCardIds) {
            $this->assertTrue($whiteCardIds->contains($item->white_card_id));
        });
    }

    /** @test */
    public function it_will_bring_back_latest_round_winner_data()
    {
        $game = $this->createGame();
        $user = $game->nonJudgeUsers()->first();
        $this->selectAndSubmitPlayerForRoundWinner($user, $game);
        $pickedCards = $user->hand()->whereSelected(true)->get();

        $winnerData = $this->gameService->roundWinner($game, $game->blackCard);

        $this->assertEquals($user->id, $winnerData['user']['id']);
        $this->assertCount($pickedCards->count(), $winnerData['userGameWhiteCards']);
        $winnerData['userGameWhiteCards']->each(function ($whiteCard) use ($pickedCards) {
            $this->assertInstanceOf(UserGameWhiteCard::class, $whiteCard);
            $this->assertTrue($pickedCards->pluck('id')->contains($whiteCard['id']));
        });
    }

    /** @test */
    public function it_will_bring_back_round_winner_data_from_a_previous_round()
    {
        Event::fake();
        $game = Game::factory()->has(Expansion::factory()->hasWhiteCards(7)->hasBlackCards(2))->hasUsers()->create();
        $this->drawBlackCard($game);
        $user = $game->nonJudgeUsers()->first();
        $this->drawCardsForUser($user, $game,7);
        $this->selectAndSubmitPlayerForRoundWinner($user, $game);
        $previousBlackCard = $game->blackCard;
        $selectedWhiteCardCount = $user->hand()->whereSelected(true)->count();
        $this->gameService->rotateGame($game);

        $winnerData = $this->gameService->roundWinner($game, $previousBlackCard);

        $this->assertEquals($user->id, $winnerData['user']['id']);
        $this->assertCount($selectedWhiteCardCount, $winnerData['userGameWhiteCards']);
    }

    /** @test */
    public function it_will_bring_back_correct_card_amount_for_each_user_after_game_rotate()
    {
        Event::fake();
        $game = Game::factory()->has(Expansion::factory()->hasBlackCards(2)->hasWhiteCards(14))->hasUsers(1)->create();
        $playerWinner = $game->nonJudgeUsers()->first();
        $this->drawBlackCard($game);
        $this->selectAndSubmitPlayerForRoundWinner($playerWinner, $game);

        $this->gameService->rotateGame($game);

        $playerWinner = $playerWinner->refresh();
        $this->assertCount(Game::HAND_LIMIT, $playerWinner->hand);
    }

    /** @test */
    public function it_will_return_randomize_submitted_cards()
    {
        $game = Game::factory()->has(Expansion::factory()->hasBlackCards()->hasWhiteCards(7))->hasUsers(4)->create();
        $this->drawBlackCard($game);
        $this->selectAllPlayersCards($game);

        $result = $this->gameService->getSubmittedCards($game);
        $responseUserIds = $result->pluck('user_id');

        $orderCount = 0;
        $previousId = 0;
        $responseUserIds->each(function ($item) use (&$orderCount, &$previousId) {

            if ($previousId and $previousId < $item) {
                $orderCount += 1;
            }
            $previousId = $item;
        });

         $this->assertCount($game->blackCardPick * 4, $result->pluck('submitted_cards'));
         $this->assertNotEquals($responseUserIds, $orderCount);
    }

    /** @test */
    public function it_will_reset_draw_count_for_all_players()
    {
        $game = Game::factory()->hasUsers(4)->create();

        $game->nonJudgeUsers->each(function ($user) {
            $user->gameState->redraw_count = 2;
            $user->gameState->save();
        });

        $this->gameService->resetDrawCount($game);

        $game->nonJudgeUsers->each(function ($user) {
            $user->gameState->refresh();
            $this->assertEquals(0, $user->gameState->redraw_count);
        });
    }
}
