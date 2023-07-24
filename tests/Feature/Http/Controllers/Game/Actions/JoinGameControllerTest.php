<?php

use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use Illuminate\Support\Facades\Event;

uses(\Tests\Traits\GameUtilities::class);

beforeEach(function () {
    Event::fake();
});

it('allows an existing player to rejoin game', function () {
    $game = Game::factory()->hasBlackCards()->hasUsers()->create();
    $player = $game->judge;

    $this->actingAs($player)->postJson(route('api.game.join', $game->code), [
        'name' => $player->name,
    ])
        ->assertOK();

    expect(User::whereName($player->name)->get())->toHaveCount(1);
});

it('adds a user to a game', function () {
    $game = Game::factory()->hasBlackCards()->hasUsers()->create();
    $joinGameResponse = $this->postJson(route('api.game.join', $game->code), [
        'name' => $this->faker->userName
    ])->assertOK();
    expect(GameUser::where('game_id', $game->id)->where('user_id', $joinGameResponse['data']['currentUser']['id'])->get())->toHaveCount(1);
});

it('gives a user white cards when joining a game', function () {
    $game = Game::factory()
        ->has(Expansion::factory()->hasWhiteCards(Game::HAND_LIMIT)->hasBlackCards(1))
        ->create();
    $this->drawBlackCard($game);

    $joinGameResponse = $this->postJson(route('api.game.join', $game->code), [
        'name' => $this->faker->userName
    ])->assertOK();

    expect(User::find($joinGameResponse->json('data.currentUser.id'))->whiteCards)->toHaveCount(Game::HAND_LIMIT);
});

it('brings back white cards that are in specific expansions', function () {
    $game = $this->createGame();
    $this->drawBlackCard($game);
    $joinGameResponse = $this->postJson(route('api.game.join', $game->code), [
        'name' => $this->faker->userName
    ])->assertOK();

    $currentExpansionIds = User::find($joinGameResponse->json('data.currentUser.id'))->whiteCards->pluck('expansion_id');

    $currentExpansionIds->each(function ($id) use ($game) {
        expect($game->expansions->pluck('id'))->toContain($id);
    });
});

it('validates user name when joining a game', function () {
    $game = Game::factory()->create();
    $this->postJson(route('api.game.join', $game->code), [
        'name' => ''
    ])->assertJsonValidationErrors([
        'name'
    ]);
});

it('prevents user from joining game that doesnt exist', function () {
    $this->postJson(route('api.game.join', 1234), [
        'name' => 'Rick Sanchez'
    ])->assertNotFound();
});

it('will allow existing player to join an existing game', function () {
    $game = $this->createGame();
    $user = User::factory()->create();
    $playerCount = $game->players()->count();

    $this->actingAs($user)
        ->postJson(route('api.game.join', $game), [
            'name' => $user->name
        ])
        ->assertOk();

    $game->refresh();

    expect($game->players()->where('users.id', $user->id)->first())->not->toBeNull();
    expect($game->players()->count())->toEqual($playerCount + 1);
});

it('returns specified json structure', function () {
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
});
