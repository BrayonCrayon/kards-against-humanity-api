<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Events\GameRotation;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Models\UserGameWhiteCard;
use App\Services\GameService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class RotateGameControllerTest extends TestCase
{
    use GameUtilities;

    /** @var Game  */
    private $game;
    public $gameService;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->gameService = new GameService();

        $this->game = Game::factory()
            ->has(Expansion::factory()->hasWhiteCards(4 * Game::HAND_LIMIT)->hasBlackCards(10))
            ->hasUsers(2)
            ->create();

        $this->drawBlackCard($this->game);
    }

    /** @test */
    public function rotating_changes_current_judge_to_new_user()
    {
        $blackCardPick = $this->game->blackCard->pick;

        $firstJudge = $this->game->judge;
        $this->selectAllPlayersCards($this->game);

        $this->actingAs($firstJudge)->postJson(route('api.game.rotate', $this->game->id))->assertOk();

        $this->game->refresh();
        $this->assertNotEquals($firstJudge->id, $this->game->judge_id);
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
        $previousBlackCard = $this->game->blackCard;

        $this->selectAllPlayersCards($this->game);

        $this->actingAs($this->game->judge)->postJson(route('api.game.rotate', $this->game->id))->assertOk();

        $this->game->refresh();
        $this->assertNotEquals($this->game->blackCard->id, $previousBlackCard->id);
    }

    /** @test */
    public function it_soft_deletes_all_submitted_white_cards()
    {
        $this->selectAllPlayersCards($this->game);

        $selectedWhiteCards = UserGameWhiteCard::whereGameId($this->game->id)->where('selected', true)->get();

        $this->actingAs($this->game->judge)->postJson(route('api.game.rotate', $this->game->id))->assertOk();

        $selectedWhiteCards->each(fn ($selectedCard) => $this->assertSoftDeleted(UserGameWhiteCard::class, [
            'id' => $selectedCard->id,
        ]));
    }


    /** @test */
    public function it_emits_event_with_new_white_cards_after_game_rotation()
    {
        $blackCardPick = $this->game->blackCard->pick;

        $this->selectAllPlayersCards($this->game);

        $this->actingAs($this->game->users->first())
            ->postJson(route('api.game.rotate', $this->game->id))
            ->assertOk();

        Event::assertDispatched(GameRotation::class, function (GameRotation $event) use ($blackCardPick) {
            return $event->game->id === $this->game->id
                && $event->broadcastOn()->name === 'game-' . $this->game->id;;
        });
    }

    /** @test */
    public function it_calls_game_service_to_rotate_game()
    {
        $serviceSpy = $this->spy(GameService::class);

        $this->selectAllPlayersCards($this->game);

        $this->actingAs($this->game->users->first())
            ->postJson(route('api.game.rotate', $this->game->id))
            ->assertOk();

        $serviceSpy->shouldHaveReceived('rotateGame')
            ->withArgs(function ($game) {
                return $game->id === $this->game->id;
            })
            ->once();
    }

    public function getNextJudge($user, $game): int
    {
        $game->users->where('id', '<>', $game->judge->id)
            ->each(fn($user) => $this->gameService->selectCards($user->whiteCards->take($game->blackCard->pick)->pluck('id'), $game, $user));

        $this->actingAs($user)->postJson(route('api.game.rotate', $game->id))->assertOk();

        $game->refresh();

        $this->assertNotEquals($user->id, $game->judge->id);
        return $game->judge->id;
    }

}
