<?php

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\WhiteCard;

test('scope query ids in brings back correct expansion', function () {
    $expansion = Expansion::factory()->create();
    expect(Expansion::idsIn([$expansion->id])->count())->toEqual(1);
    expect(Expansion::idsIn([$expansion->id])->first()->id)->toEqual($expansion->id);
});

test('white cards relationship brings back white card type', function () {
    $expansion = Expansion::factory()->hasWhiteCards()->create();
    expect($expansion->whiteCards->first())->toBeInstanceOf(WhiteCard::class);
});

test('black cards relationship brings back black card types', function () {
    $expansion = Expansion::factory()->hasBlackCards()->create();
    expect($expansion->blackCards->first())->toBeInstanceOf(BlackCard::class);
});
