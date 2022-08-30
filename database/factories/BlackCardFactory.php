<?php

namespace Database\Factories;

use App\Models\Expansion;
use Illuminate\Database\Eloquent\Factories\Factory;

class BlackCardFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'text' => $this->faker->text(),
            'pick' => 1,
            'expansion_id' => Expansion::factory(),
        ];
    }

    public function pickOf2()
    {
        return $this->state(function() {
           return [
               'pick' => 2
           ];
        });
    }
}
