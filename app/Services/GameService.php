<?php


namespace App\Services;


use App\Models\Expansion;
use App\Models\Game;
use App\Models\UserGameWhiteCards;
use App\Models\WhiteCard;
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

        $this->grabWhiteCards($user, $game);

        return $game;
    }

    public function grabWhiteCards($user, $game)
    {
        $pickedCards = WhiteCard::inRandomOrder()->limit(Game::HAND_LIMIT)->get();

        $pickedCards->each(fn ($item) =>
            UserGameWhiteCards::create([
                'game_id' => $game->id,
                'user_id' => $user->id,
                'white_card_id' => $item->id
            ])
        );
    }
}
