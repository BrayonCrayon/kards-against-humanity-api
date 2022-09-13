<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Tests\Traits\GameUtilities;

class JoinGameControllerTest extends TestCase
{
    use GameUtilities;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
    }

    /** @test */
    public function it_allows_an_existing_player_to_rejoin_game()
    {
        $game = Game::factory()->hasBlackCards()->hasUsers()->create();
        $player = $game->judge;

        $this->actingAs($player)->postJson(route('api.game.join', $game->code), [
            'name' => $player->name,
        ])
            ->assertOK();

        $this->assertCount(1, User::whereName($player->name)->get());
    }


    /** @test */
    public function it_adds_a_user_to_a_game()
    {
        $game = Game::factory()->hasBlackCards()->hasUsers()->create();
        $joinGameResponse = $this->postJson(route('api.game.join', $game->code), [
            'name' => $this->faker->userName
        ])->assertOK();
        $this->assertCount(1, GameUser::where('game_id', $game->id)->where('user_id', $joinGameResponse['data']['currentUser']['id'])->get());
    }

    /** @test */
    public function it_gives_a_user_white_cards_when_joining_a_game()
    {
        $game = Game::factory()
            ->has(Expansion::factory()->hasWhiteCards(Game::HAND_LIMIT)->hasBlackCards(1))
            ->create();
        $this->drawBlackCard($game);

        $joinGameResponse = $this->postJson(route('api.game.join', $game->code), [
            'name' => $this->faker->userName
        ])->assertOK();

        $this->assertCount(Game::HAND_LIMIT, User::find($joinGameResponse->json('data.currentUser.id'))->whiteCards);
    }

    /** @test */
    public function it_brings_back_white_cards_that_are_in_specific_expansions()
    {
        $game = $this->createGame();
        $this->drawBlackCard($game);
        $joinGameResponse = $this->postJson(route('api.game.join', $game->code), [
            'name' => $this->faker->userName
        ])->assertOK();

        $currentExpansionIds = User::find($joinGameResponse->json('data.currentUser.id'))->whiteCards->pluck('expansion_id');

        $currentExpansionIds->each(function ($id) use ($game) {
            $this->assertContains($id, $game->expansions->pluck('id'));
        });
    }

    /** @test */
    public function it_validates_user_name_when_joining_a_game()
    {
        $game = Game::factory()->create();
        $this->postJson(route('api.game.join', $game->code), [
            'name' => ''
        ])->assertJsonValidationErrors([
            'name'
        ]);
    }

    /** @test */
    public function it_prevents_user_from_joining_game_that_doesnt_exist()
    {
        $this->postJson(route('api.game.join', 1234), [
            'name' => 'Rick Sanchez'
        ])->assertNotFound();
    }

    /** @test */
    public function it_will_allow_existing_player_to_join_an_existing_game()
    {
        $game = $this->createGame();
        $user = User::factory()->create();
        $playerCount = $game->players()->count();

        $this->actingAs($user)
            ->postJson(route('api.game.join', $game), [
                'name' => $user->name
            ])
            ->assertOk();

        $game->refresh();

        $this->assertNotNull($game->players()->where('users.id', $user->id)->first());
        $this->assertEquals($playerCount + 1, $game->players()->count());
    }

    /** @test */
    public function it_returns_specified_json_structure()
    {
        $game = Game::factory()
            ->has(Expansion::factory()->hasWhiteCards(Game::HAND_LIMIT)->hasBlackCards(1))
            ->create();
        $this->drawBlackCard($game);

        $this->postJson(route('api.game.join', $game->code), [
            'name' => 'foo'
        ])->assertOk()
            ->assertJsonStructure([
                    'data' => [
                        'users' => [
                            [
                                'id',
                                'name',
                            ]
                        ],
                        'currentUser' => [
                            'id',
                            'name',
                        ],
                        'hand' => [
                            [
                                'id',
                                'text',
                                'expansionId'
                            ],
                        ],
                        'game' => [
                            'id',
                            'name',
                            'code',
                            'judgeId',
                            'redrawLimit'
                        ],
                        'blackCard' => [
                            'id',
                            'text',
                            'pick'
                        ]
                    ]
                ]
            );
    }
}
