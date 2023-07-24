<?php

use App\Models\Expansion;
use App\Models\WhiteCard;

test('expansion relationship brings back expansion type', function () {
    $whiteCard = WhiteCard::factory()->create();
    expect($whiteCard->expansion)->toBeInstanceOf(Expansion::class);
});
