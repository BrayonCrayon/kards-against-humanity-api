<?php

namespace Tests\Feature\Models\Game;

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Models\GameBlackCards;
use App\Models\UserGameWhiteCard;
use App\Models\WhiteCard;
use Tests\TestCase;

class GameTest extends TestCase
{

    /** @test */
    public function user_relationship_brings_back_user_type()
    {
        $game = Game::factory()
            ->hasUsers(1)
            ->create();
        $this->assertInstanceOf(User::class, $game->users->first());
    }

    /** @test */
    public function expansion_relationship_brings_back_expansion_type()
    {
        $game = Game::factory()
            ->hasAttached(Expansion::factory())
            ->create();
        $this->assertInstanceOf(Expansion::class, $game->expansions->first());
    }

    /** @test */
    public function it_has_a_judge()
    {
        $game = Game::factory()->create();

        $this->assertInstanceOf(User::class, $game->judge);
    }

    /** @test */
    public function it_can_get_a_black_card()
    {
        $blackCard = BlackCard::factory()->create();
        $game = Game::factory()->hasUsers(2)->create();

        GameBlackCards::create([
            'black_card_id' => $blackCard->id,
            'game_id' => $game->id,
        ]);

        $this->assertInstanceOf(BlackCard::class, $game->blackCard);
    }

    /** @test */
    public function it_can_get_black_cards()
    {
        $game = Game::factory()->create();

        $blackCard = BlackCard::factory()->create();
        $game->blackCards()->attach($blackCard);

        $this->assertEquals($blackCard->id, $game->blackCards->first()->id);
    }

    /** @test */
    public function it_brings_back_users_that_are_not_a_judge_user()
    {
        $usersToCreate = 3;
        $game = Game::factory()->hasUsers($usersToCreate)->create();

        $users = $game->nonJudgeUsers()->get()->pluck('id');

        $this->assertCount($usersToCreate, $users);
        $this->assertFalse(in_array($game->judge_id, $users->toArray()));
    }

    /** @test */
    public function it_returns_correct_black_pick_amount_from_game_attribute()
    {
        $game = Game::factory()->hasBlackCards()->create();

        $this->assertEquals($game->blackCard->pick, $game->blackCardPick);
    }

    /** @test */
    public function it_can_get_white_cards()
    {
        $game = Game::factory()->has(Expansion::factory()->hasWhiteCards())->create();

        $whiteCard = WhiteCard::firstOrFail();

        $this->assertEquals($whiteCard->id, $game->available_white_cards->first()->id);
    }

    /** @test */
    public function it_excludes_white_cards_that_have_been_drawn()
    {
        $game = Game::factory()->has(Expansion::factory()->hasWhiteCards(2))->create();

        [$whiteCard, $drawnWhiteCard] = WhiteCard::all();

        UserGameWhiteCard::create([
            'white_card_id' => $drawnWhiteCard->id,
            'user_id' => $game->judge->id,
            'game_id' => $game->id,
        ]);

        $this->assertCount(1, $game->available_white_cards);
//        $this->assertEquals($whiteCard->id, $game->available_white_cards->first()->id);
    }
}
