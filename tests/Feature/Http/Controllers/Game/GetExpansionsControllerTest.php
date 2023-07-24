<?php

use App\Models\Expansion;

it('can fetch expansions', function () {
    $expectedExpansion = Expansion::factory()->create();

    $this->getJson(route('api.expansions.index'))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonFragment([
            'name' => $expectedExpansion->name,
            'cardCount' => $expectedExpansion->card_count,
            'created_at' => $expectedExpansion->created_at,
            'updated_at' => $expectedExpansion->updated_at,
        ]);
});
