<?php

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\User;
use App\Models\WhiteCard;
use Illuminate\Support\Str;
use function Pest\Laravel\{postJson};

it('does not allow empty user name', function () {
    $expansionIds = Expansion::take(3)->get()->pluck('id');

    expect(postJson(route('api.game.store'), [
        'name' => "",
        'expansionIds' => $expansionIds->toArray()
    ]))->assertUnprocessable();
});

it('does not allow user to select no expansions', function () {
    $user = User::factory()->make();
    expect(postJson(route('api.game.store'), [
        'name' => $user->name,
        'expansionIds' => []
    ]))->assertUnprocessable();
});

it('does not allow undefined expansions', function () {
    $user = User::factory()->make();
    expect(postJson(route('api.game.store'), [
        'name' => $user->name,
        'expansionIds' => [-1]
    ]))->assertUnprocessable();
});

it('creates user', function () {
    $userName = $this->faker->userName;
    $expansionIds = Expansion::factory(2)->has(BlackCard::factory())->create()->pluck('id');

    expect(postJson(route('api.game.store'), ['name' => $userName, 'expansionIds' => $expansionIds->toArray()]))
        ->toBeOk()
        ->and(['name' => $userName])
        ->toBeInDatabase(table: 'users');
});

it('creates game', function () {
    $userName = $this->faker->userName;
    $expansionIds = Expansion::factory(2)->has(BlackCard::factory())->create()->pluck('id');
    $response = postJson(route('api.game.store'), [
        'name' => $userName,
        'expansionIds' => $expansionIds->toArray()
    ])->assertOk();

    expect([
        'id' => $response->json('data.game.id'),
        'name' => $response->json('data.game.name'),
        'code' => $response->json('data.game.code'),
        'redraw_limit' => 2
    ])->toBeInDatabase('games')
    ->and(strlen($response->json('data.game.code')))->toEqual(4);
});

it('assigns users when game is created', function () {
    $userName = $this->faker->userName;
    $expansionIds = Expansion::factory(2)->has(BlackCard::factory())->create()->pluck('id');
    $response = postJson(route('api.game.store'), [
        'name' => $userName,
        'expansionIds' => $expansionIds->toArray()
    ])->assertOk();

    expect([
        'game_id' => $response->json('data.game.id'),
        'user_id' => $response->json('data.currentUser.id')
    ])->toBeInDatabase('game_users');
});

it('gives users cards when a game is created', function () {
    $userName = $this->faker->userName;
    $expansion = Expansion::factory()
        ->has(BlackCard::factory())
        ->has(WhiteCard::factory(7))
        ->create();

    expect(postJson(route('api.game.store'), [
        'name' => $userName,
        'expansionIds' => [$expansion->id]
    ]))->toBeOk();

    $createdUser = User::where('name', $userName)->first();

    expect($createdUser->whiteCards)->toHaveCount(7);
    $createdUser->whiteCards->each(function ($item) use ($expansion) {
        expect($item->expansion_id)->toEqual($expansion->id);
    });
});

it('assigns selected expansions when game is created', function () {
    $userName = $this->faker->userName;
    $id = Expansion::factory()->has(BlackCard::factory())->create()->id;
    $response = postJson(route('api.game.store'), [
        'name' => $userName,
        'expansionIds' => [$id]
    ])->assertOk();

    expect([
        'game_id' => $response->json('data.game.id'),
        'expansion_id' => $id
    ])->toBeInDatabase('game_expansions');
});

it('creates game code with uppercase letters', function () {
    $userName = $this->faker->userName;
    $response = postJson(route('api.game.store'), [
        'name' => $userName,
        'expansionIds' => [Expansion::factory()->has(BlackCard::factory())->create()->id]
    ]);

    $gameCode = $response->json('data.game.code');
    expect($gameCode)->toEqual(Str::upper($gameCode));

    $invalidCode = Str::lower($gameCode);
    expect($invalidCode)->not->toEqual($gameCode);
});

it('expects certain shape', function () {
    $userName = $this->faker->userName;
    $id = Expansion::factory()->hasBlackCards()->hasWhiteCards(7)->create()->id;
    expect(postJson(route('api.game.store'), [
        'name' => $userName,
        'expansionIds' => [$id]
    ]))->toBeOk()
        ->toHaveJsonStructure([
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
