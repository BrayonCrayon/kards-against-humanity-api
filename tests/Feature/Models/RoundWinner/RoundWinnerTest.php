<?php

use App\Models\BlackCard;
use App\Models\Game;
use App\Models\RoundWinner;
use App\Models\User;
use App\Models\WhiteCard;

it('has relationships', function () {
    $roundWinner = RoundWinner::factory()->create();

    expect($roundWinner->user)->toBeInstanceOf(User::class);
    expect($roundWinner->game)->toBeInstanceOf(Game::class);
    expect($roundWinner->whiteCard)->toBeInstanceOf(WhiteCard::class);
    expect($roundWinner->blackCard)->toBeInstanceOf(BlackCard::class);
});
