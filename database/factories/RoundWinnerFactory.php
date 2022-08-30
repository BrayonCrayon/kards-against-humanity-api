<?php

namespace Database\Factories;

use App\Models\BlackCard;
use App\Models\Game;
use App\Models\RoundWinner;
use App\Models\User;
use App\Models\WhiteCard;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoundWinnerFactory extends Factory
{

    /** @var string  */
    protected $model = RoundWinner::class;

    public function definition() : array
    {
        return [
            'user_id' => User::factory(),
            'white_card_id' => WhiteCard::factory(),
            'black_card_id' => BlackCard::factory(),
            'game_id' => Game::factory()
        ];
    }
}
