<?php

namespace Database\Factories;

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\User;
use App\Services\GameService;
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
            'code' => $this->faker->bothify('##??'),
            'judge_id' => User::factory(),
            'redraw_limit' => 2
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Game $game) {
            $gameService = new GameService();
            $game->expansions()->saveMany([Expansion::first()]);
            $game->blackCards()->attach(BlackCard::first());
            $game->users()->attach($game->judge);
            $game->users->each(fn($user) => $gameService->drawWhiteCards($user, $game));
        });
    }
}
