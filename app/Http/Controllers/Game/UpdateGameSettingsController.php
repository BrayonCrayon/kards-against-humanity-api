<?php

namespace App\Http\Controllers\Game;

use App\Http\Requests\UpdateGameSettingsRequest;
use App\Models\Game;

class UpdateGameSettingsController
{
    public function __invoke(UpdateGameSettingsRequest $request, Game $game): void
    {
        $game->setting->update($request->validated());
    }
}
