<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\User;
use App\Models\UserGameWhiteCard;
use App\Models\WhiteCard;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserGameWhiteCardFactory extends Factory
{
    /** @var string  */
    protected $model = UserGameWhiteCard::class;

    public function definition() : array
    {
        return [
            'user_id' => User::factory(),
            'game_id' => Game::factory(),
            'white_card_id' => WhiteCard::factory()
        ];
    }

    public function selected() : self {
        return $this->state(fn () => [
           'selected' => true,
        ]);
    }
}
