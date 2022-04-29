<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use App\Services\GameService;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class JoinGameControllerTest extends TestCase
{
    private $game;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        $this->game = Game::factory()->hasUsers(1)->create();
    }

    /** @test */
    public function it_allows_an_existing_player_to_rejoin_game()
    {
        $player = $this->game->nonJudgeUsers()->first();
        Sanctum::actingAs($player, []);

        $this->postJson(route('api.game.join', $this->game->code), [
            'name' => $player->name,
        ])
        ->assertOK();

        $this->assertCount(1, User::whereName($player->name)->get());
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
            $this->assertContains($id, $this->game->expansions->pluck('id'));
        });
    }

    /** @test */
    public function it_validates_user_name_when_joining_a_game()
    {
        $this->postJson(route('api.game.join', $this->game->code), [
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
                    'redrawLimit',
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
