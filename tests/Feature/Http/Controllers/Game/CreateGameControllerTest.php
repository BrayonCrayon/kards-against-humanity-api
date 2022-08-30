<?php

namespace Tests\Feature\Http\Controllers\Game;

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\User;
use App\Models\WhiteCard;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Tests\TestCase;

class CreateGameControllerTest extends TestCase
{

    /** @test */
    public function it_does_not_allow_empty_user_name()
    {
        $expansionIds = Expansion::take(3)->get()->pluck('id');
        $this->postJson(route('api.game.store'), [
            'name' => "",
            'expansionIds' => $expansionIds->toArray()
        ])->assertUnprocessable();
    }

    /** @test */
    public function it_does_not_allow_user_to_select_no_expansions()
    {
        $user = User::factory()->make();
        $this->postJson(route('api.game.store'), [
            'name' => $user->name,
            'expansionIds' => []
        ])->assertUnprocessable();
    }

    /** @test */
    public function it_does_not_allow_undefined_expansions()
    {
        $user = User::factory()->make();
        $this->postJson(route('api.game.store'), [
            'name' => $user->name,
            'expansionIds' => [-1]
        ])->assertUnprocessable();
    }

    /** @test */
    public function it_creates_user()
    {
        $userName = $this->faker->userName;
        $expansionIds = Expansion::factory(2)->has(BlackCard::factory())->create()->pluck('id');

        $this->postJson(route('api.game.store'), [
            'name' => $userName,
            'expansionIds' => $expansionIds->toArray()
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'name' => $userName
        ]);
    }

    /** @test */
    public function it_creates_game()
    {
        $userName = $this->faker->userName;
        $expansionIds = Expansion::factory(2)->has(BlackCard::factory())->create()->pluck('id');
        $response = $this->postJson(route('api.game.store'), [
            'name' => $userName,
            'expansionIds' => $expansionIds->toArray()
        ])->assertOk();

        $this->assertDatabaseHas('games', [
            'id' => $response->json('data.game.id'),
            'name' => $response->json('data.game.name'),
            'code' => $response->json('data.game.code'),
            'redraw_limit' => 2
        ]);

        $this->assertEquals(4, strlen($response->json('data.game.code')));
    }

    /** @test */
    public function it_assigns_users_when_game_is_created()
    {
        $userName = $this->faker->userName;
        $expansionIds = Expansion::factory(2)->has(BlackCard::factory())->create()->pluck('id');
        $response = $this->postJson(route('api.game.store'), [
            'name' => $userName,
            'expansionIds' => $expansionIds->toArray()
        ])->assertOk();

        $this->assertDatabaseHas('game_users', [
            'game_id' => $response->json('data.game.id'),
            'user_id' => $response->json('data.currentUser.id')
        ]);
    }

    /** @test */
    public function it_gives_users_cards_when_a_game_is_created()
    {
        $userName = $this->faker->userName;
        $expansion = Expansion::factory()
            ->has(BlackCard::factory())
            ->has(WhiteCard::factory(7))
            ->create();

        $this->postJson(route('api.game.store'), [
            'name' => $userName,
            'expansionIds' => [$expansion->id]
        ])->assertOk();

        $createdUser = User::where('name', $userName)->first();

        $this->assertCount(7, $createdUser->whiteCards);
        $createdUser->whiteCards->each(function ($item) use ($expansion) {
            $this->assertEquals($expansion->id, $item->expansion_id);
        });
    }

    /** @test */
    public function it_assigns_selected_expansions_when_game_is_created()
    {
        $userName = $this->faker->userName;
        $id = Expansion::factory()->has(BlackCard::factory())->create()->id;
        $response = $this->postJson(route('api.game.store'), [
            'name' => $userName,
            'expansionIds' => [$id]
        ])->assertOk();

        $this->assertDatabaseHas('game_expansions', [
            'game_id' => $response->json('data.game.id'),
            'expansion_id' => $id
        ]);
    }
    /** @test */
    public function it_creates_game_code_with_uppercase_letters()
    {
        $userName = $this->faker->userName;
        $response = $this->postJson(route('api.game.store'), [
            'name' => $userName,
            'expansionIds' => [Expansion::factory()->has(BlackCard::factory())->create()->id]
        ]);

        $gameCode = $response->json('data.game.code');
        $this->assertEquals(Str::upper($gameCode), $gameCode);

        $invalidCode = Str::lower($gameCode);
        $this->assertNotEquals($invalidCode, $gameCode);
    }

    /** @test */
    public function it_expects_certain_shape()
    {
        $userName = $this->faker->userName;
        $id = Expansion::factory()->hasBlackCards()->hasWhiteCards(7)->create()->id;
        $this->postJson(route('api.game.store'), [
            'name' => $userName,
            'expansionIds' => [$id]
        ])->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'users' => [
                        [
                            'id',
                            'name',
                            'score'
                        ]
                    ],
                    'currentUser' => [
                        'id',
                        'name',
                        'score'
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
                        'redrawLimit',
                        'judgeId'
                    ],
                    'blackCard' => [
                        'id',
                        'text',
                        'pick'
                    ]
                ]
            ]);
    }
}
