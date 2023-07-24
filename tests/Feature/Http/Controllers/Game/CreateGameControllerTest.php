<?php

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\User;
use App\Models\WhiteCard;
use Illuminate\Support\Str;

it('does not allow empty user name', function () {
    $expansionIds = Expansion::take(3)->get()->pluck('id');
    $this->postJson(route('api.game.store'), [
        'name' => "",
        'expansionIds' => $expansionIds->toArray()
    ])->assertUnprocessable();
});

it('does not allow user to select no expansions', function () {
    $user = User::factory()->make();
    $this->postJson(route('api.game.store'), [
        'name' => $user->name,
        'expansionIds' => []
    ])->assertUnprocessable();
});

it('does not allow undefined expansions', function () {
    $user = User::factory()->make();
    $this->postJson(route('api.game.store'), [
        'name' => $user->name,
        'expansionIds' => [-1]
    ])->assertUnprocessable();
});

it('creates user', function () {
    $userName = $this->faker->userName;
    $expansionIds = Expansion::factory(2)->has(BlackCard::factory())->create()->pluck('id');

    $this->postJson(route('api.game.store'), [
        'name' => $userName,
        'expansionIds' => $expansionIds->toArray()
    ])->assertOk();

    $this->assertDatabaseHas('users', [
        'name' => $userName
    ]);
});

it('creates game', function () {
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

    expect(strlen($response->json('data.game.code')))->toEqual(4);
});

it('assigns users when game is created', function () {
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
});

it('gives users cards when a game is created', function () {
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

    expect($createdUser->whiteCards)->toHaveCount(7);
    $createdUser->whiteCards->each(function ($item) use ($expansion) {
        expect($item->expansion_id)->toEqual($expansion->id);
    });
});

it('assigns selected expansions when game is created', function () {
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
});

it('creates game code with uppercase letters', function () {
    $userName = $this->faker->userName;
    $response = $this->postJson(route('api.game.store'), [
        'name' => $userName,
        'expansionIds' => [Expansion::factory()->has(BlackCard::factory())->create()->id]
    ]);

    $gameCode = $response->json('data.game.code');
    expect($gameCode)->toEqual(Str::upper($gameCode));

    $invalidCode = Str::lower($gameCode);
    $this->assertNotEquals($invalidCode, $gameCode);
});

it('expects certain shape', function () {
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
});
