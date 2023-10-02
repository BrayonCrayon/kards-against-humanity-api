<?php

use App\Models\Expansion;
use function Pest\Laravel\{getJson};

it('can fetch expansions', function () {
    $expectedExpansion = Expansion::factory()->create();

    expect(getJson(route('api.expansions.index')))
        ->toBeOk()
        ->assertJsonCount(1, 'data')
        ->toHaveJsonFragment([
            'name' => $expectedExpansion->name,
            'cardCount' => $expectedExpansion->card_count,
            'created_at' => $expectedExpansion->created_at,
            'updated_at' => $expectedExpansion->updated_at,
        ]);
});
