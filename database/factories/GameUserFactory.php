<?php

namespace Database\Factories;

use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GameUserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GameUser::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'game_id' => Game::factory()
        ];
    }
}
