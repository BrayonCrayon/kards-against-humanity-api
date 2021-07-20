<?php


namespace App\Services;


use App\Models\Expansion;
use App\Models\Game;
use Nubs\RandomNameGenerator\All as NameGenerator;

class GameService
{
    private $generator;

    public function __construct()
    {
        $this->generator = NameGenerator::create();
    }

    public function createGame($user, $expansionIds) {
        $game = Game::create([
            'name' => $this->generator->getName()
        ]);

        $game->users()->save($user);

        $game->expansions()->saveMany(Expansion::idsIn($expansionIds)->get());

        return $game;
    }
}
