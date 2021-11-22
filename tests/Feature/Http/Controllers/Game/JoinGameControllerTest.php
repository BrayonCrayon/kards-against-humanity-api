<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class JoinGameControllerTest extends TestCase
{
    private $game;
    private $expansionIds;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->expansionIds = [Expansion::first()->id];
        $user = User::factory()->create();
        $this->game = Game::factory()->create();
        $this->game->users()->save($user);
        $this->game->expansions()->saveMany(Expansion::idsIn($this->expansionIds)->get());

        $gameService = new GameService();
        $gameService->drawBlackCard($this->game);
    }

    /** @test */
    public function it_adds_a_user_to_a_game()
    {
        $joinGameResponse = $this->postJson(route('api.game.join', $this->game->code), [
            'name' => $this->faker->userName
        ])->assertOK();
        $this->assertCount(1, GameUser::where('game_id', $this->game->id)->where('user_id', $joinGameResponse['data']['current_user']['id'])->get());
    }

    /** @test */
    public function it_gives_a_user_white_cards_when_joining_a_game()
    {
        $joinGameResponse = $this->postJson(route('api.game.join', $this->game->code), [
            'name' => $this->faker->userName
        ])->assertOK();
        $this->assertCount(Game::HAND_LIMIT, User::find($joinGameResponse->json('data.current_user.id'))->whiteCards);
    }

    /** @test */
    public function it_brings_back_white_cards_that_are_in_specific_expansions()
    {
        $joinGameResponse = $this->postJson(route('api.game.join', $this->game->code), [
            'name' => $this->faker->userName
        ])->assertOK();

        $currentExpansionIds = User::find($joinGameResponse->json('data.current_user.id'))->whiteCards->pluck('expansion_id');

        $currentExpansionIds->each(function ($id) {
            $this->assertContains($id, $this->expansionIds);
        });
    }

    /** @test */
    public function it_validates_user_name_when_joining_a_game()
    {
        $joinGameResponse = $this->postJson(route('api.game.join', $this->game->code), [
            'name' => ''
        ])->assertJsonValidationErrors([
            'name'
        ]);
    }

    /** @test */
    public function it_prevents_user_from_joining_game_that_doesnt_exist()
    {
        $joinGameResponse = $this->postJson(route('api.game.join', 1234), [
            'name' => 'Rick Sanchez'
        ])->assertNotFound();
    }

    /** @test */
    public function it_returns_specified_json_structure()
    {
        $this->postJson(route('api.game.join', $this->game->code), [
            'name' => 'foo'
        ])->assertJsonStructure([
                'data' => [
                    'users' => [
                        [
                            'id',
                            'name',
                        ]
                    ],
                    'current_user' => [
                        'id',
                        'name',
                    ],
                    'judge' => [
                        'id',
                        'name',
                    ],
                    'hand' => [
                        [
                            'id',
                            'text',
                            'expansion_id'
                        ],
                    ],
                    'id',
                    'name',
                    'code',
                    'current_black_card' => [
                        'id',
                        'text',
                        'pick'
                    ]
                ]
            ]
        );
    }
}
