<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use App\Models\GameBlackCards;
use App\Services\GameService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RoundRotationTest extends TestCase
{
    private $game;
    private $expansionIds;

    protected function setUp(): void
    {
        parent::setUp();
        $gameService = new GameService();
        $this->expansionIds = [Expansion::first()->id];
        $users = User::factory(5)->create();
        $this->game = Game::factory()->create([
            'judge_id' => $users->first()->id,
        ]);
        foreach ($users as $user) {
            $gameService->grabWhiteCards($user, $this->game, $this->expansionIds);
            $this->game->users()->save($user);
        }
        $this->game->expansions()->saveMany(Expansion::idsIn($this->expansionIds)->get());
        $gameService->grabBlackCards($this->game, $this->expansionIds);
    }

    /** @test */
    public function rotating_changes_current_judge_to_new_user()
    {
        $blackCardPick = $this->game->gameBlackCards()->first()->blackCard->pick;

        $firstJudge = $this->game->judge;
        $this->game->users->each(function($user) use($blackCardPick) {
            $userCards = $user->whiteCardsInGame->take($blackCardPick);
            $userCards->each(fn ($card) => $card->update(['selected' => true]));
        });

        $this->postJson(route('api.game.rotate', $this->game->id))->assertOk();

        $this->game->refresh();
        $this->assertNotEquals($firstJudge->id, $this->game->judge_id);
    }

    /** @test */
    public function it_cycles_through_the_users_when_assigning_the_judge_when_number_of_users_is_odd()
    {
        $blackCardPick = $this->game->gameBlackCards()->first()->blackCard->pick;

        $pickedJudgeIds = collect();

        $this->game->users->each(function ($user) use ($blackCardPick, $pickedJudgeIds) {

            $this->game->users->each(function($user) use($blackCardPick) {
                $userCards = $user->whiteCardsInGame->take($blackCardPick);
                $userCards->each(fn ($card) => $card->update(['selected' => true]));
            });

            $this->postJson(route('api.game.rotate', $this->game->id))->assertOk();

            $this->game->refresh();

            $this->assertNotEquals($user->id, $this->game->judge->id);

            $pickedJudgeIds->add($this->game->judge_id);
        });


        $this->assertCount($this->game->users->count(), $pickedJudgeIds->unique()->all());
    }

    /** @test */
    public function it_cycles_through_the_users_when_assigning_the_judge_when_number_of_users_is_even()
    {
        $newUser = User::factory()->create();
        $this->game->users()->save($newUser);
        $blackCardPick = $this->game->gameBlackCards()->first()->blackCard->pick;

        $pickedJudgeIds = collect();

        $this->game->refresh();
        $this->game->users->each(function ($user) use ($blackCardPick, $pickedJudgeIds) {

            $this->game->users->each(function($user) use($blackCardPick) {
                $userCards = $user->whiteCardsInGame->take($blackCardPick);
                $userCards->each(fn ($card) => $card->update(['selected' => true]));
            });

            $this->postJson(route('api.game.rotate', $this->game->id))->assertOk();

            $this->game->refresh();

            $this->assertNotEquals($user->id, $this->game->judge->id);

            $pickedJudgeIds->add($this->game->judge_id);
        });


        $this->assertCount($this->game->users->count(), $pickedJudgeIds->unique()->all());
    }

    /** @test */
    public function it_gives_new_black_card_after_game_rotation()
    {
        /** @var Game $game */
        $game = $this->game;
        $blackCardPick = $game->gameBlackCards()->first()->blackCard->pick;
        $previousBlackCard = $game->currentBlackCard;

        $game->users->each(function($user) use($blackCardPick) {
            $userCards = $user->whiteCardsInGame->take($blackCardPick);
            $userCards->each(fn ($card) => $card->update(['selected' => true]));
        });

        $this->postJson(route('api.game.rotate', $game->id))->assertOk();

        $game->refresh();
        $this->assertNotEquals($game->currentBlackCard->id, $previousBlackCard->id);
    }
}
