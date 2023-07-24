<?php

use App\Models\Expansion;

it('add correct white card count to expansion', function () {
    $expansions = Expansion::factory()
        ->count(2)
        ->hasBlackCards(5)
        ->hasWhiteCards(5)
        ->create();

    $expansions->each(fn($expansion) => expect($expansion->card_count)->toEqual(0));

    $this->artisan('kah:count-cards');

    $expansions->each(fn($expansion) => expect($expansion->fresh()->card_count)->toEqual(10));
});
