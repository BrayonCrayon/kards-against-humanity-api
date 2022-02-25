<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Events\GameRotation;
use App\Models\Game;
use App\Models\User;
use App\Models\UserGameWhiteCards;
use App\Services\GameService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class RotateGameControllerTest extends TestCase
{
    /** @var Game  */
    private $game;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();

        $this->game = Game::factory()->hasUsers(2)->create();
    }

    /** @test */
    public function rotating_changes_current_judge_to_new_user()
    {
        $blackCardPick = $this->game->currentBlackCard->pick;

        $firstJudge = $this->game->judge;
        $this->playersSubmitCards($blackCardPick, $this->game);

        $this->actingAs($firstJudge)->postJson(route('api.game.rotate', $this->game->id))->assertOk();

        $this->game->refresh();
        $this->assertNotEquals($firstJudge->id, $this->game->judge_id);
    }

    /** @test */
    public function it_cycles_through_the_users_when_assigning_the_judge_when_number_of_users_is_odd()
    {
        $pickedJudgeIds = collect();

        $this->game->users->each(fn($user) => $pickedJudgeIds->add($this->getNextJudge($user, $this->game)));

        $this->assertCount($this->game->users->count(), $pickedJudgeIds->unique()->all());
    }

    /** @test */
    public function it_cycles_through_the_users_when_assigning_the_judge_when_number_of_users_is_even()
    {
        $newUser = User::factory()->create();
        $this->gameService->joinGame($this->game, $newUser);

        $pickedJudgeIds = collect();

        $this->game->refresh();
        $this->game->users->each(fn($user) => $pickedJudgeIds->add($this->getNextJudge($user, $this->game)));


        $this->assertCount($this->game->users->count(), $pickedJudgeIds->unique()->all());
    }

    /** @test */
    public function it_gives_new_black_card_after_game_rotation()
    {
        $previousBlackCard = $this->game->currentBlackCard;

        $this->playersSubmitCards($this->game->currentBlackCard->pick, $this->game);

        $this->actingAs($this->game->judge)->postJson(route('api.game.rotate', $this->game->id))->assertOk();

        $this->game->refresh();
        $this->assertNotEquals($this->game->currentBlackCard->id, $previousBlackCard->id);
    }

    /** @test */
    public function it_soft_deletes_all_submitted_white_cards()
    {
        $this->playersSubmitCards($this->game->currentBlackCard->pick, $this->game);

        $selectedWhiteCards = UserGameWhiteCards::whereGameId($this->game->id)->where('selected', true)->get();

        $this->actingAs($this->game->judge)->postJson(route('api.game.rotate', $this->game->id))->assertOk();

        $selectedWhiteCards->each(fn ($selectedCard) => $this->assertSoftDeleted(UserGameWhiteCards::class, [
            'id' => $selectedCard->id,
        ]));
    }


    /** @test */
    public function it_emits_event_with_new_white_cards_after_game_rotation()
    {
        $blackCardPick = $this->game->currentBlackCard->pick;

        $this->playersSubmitCards($blackCardPick, $this->game);

        $this->actingAs($this->game->users->first())
            ->postJson(route('api.game.rotate', $this->game->id))
            ->assertOk();

        $this->game->users->each(function($user) use ($blackCardPick) {
            Event::assertDispatched(GameRotation::class, function (GameRotation $event) use ($blackCardPick, $user) {
                return
                    ($event->user->whiteCardsInGame->toArray() != null)
                    && Game::HAND_LIMIT === count($event->user->whiteCardsInGame->toArray())
                    && $event->game->id === $this->game->id
                    && $event->broadcastOn()->name === 'private-game.' . $this->game->id . '.' . $user->id;
            });
        });
    }

    /** @test */
    public function it_calls_game_service_to_rotate_game()
    {
        $serviceSpy = $this->spy(GameService::class);

        $this->playersSubmitCards($this->game->blackCardPick, $this->game);

        $this->actingAs($this->game->users->first())
            ->postJson(route('api.game.rotate', $this->game->id))
            ->assertOk();

        $serviceSpy->shouldHaveReceived('rotateGame')
            ->withArgs(function ($game) {
                return $game->id === $this->game->id;
            })
            ->once();
    }


}
