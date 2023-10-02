<?php

use App\Models\BlackCard;
use App\Models\Expansion;

test('expansion relationship brings back expansion type', function () {
    $blackCard = BlackCard::factory()->create();
    expect($blackCard->expansion)->toBeInstanceOf(Expansion::class);
});
