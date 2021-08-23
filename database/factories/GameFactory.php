<?php

namespace Database\Factories;

use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\WithFaker;
use Nubs\RandomNameGenerator\All;
class GameFactory extends Factory
{

    use WithFaker;
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Game::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $generator = All::create();
        return [
            'id' => $this->faker->uuid(),
            'name' => $generator->getName(),
            'judge_id' => User::factory()
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Game $game) {
            $game->expansions()->saveMany([Expansion::first()]);
        });
    }
}
