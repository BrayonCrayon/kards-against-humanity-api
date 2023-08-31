<?php

namespace Database\Factories;

use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;

class SettingFactory extends Factory
{
    public function definition() : array
    {
        return [
            'game_id' => Game::factory(),
        ];
    }

    public function withTimer(int $selectionTimer) : self
    {
        return $this->state(fn() => ['selection_timer' => $selectionTimer]);
    }
}
