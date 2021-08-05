<?php


namespace App\Services;


use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use App\Models\UserGameBlackCards;
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

        $this->grabWhiteCards($user, $game, $expansionIds);
        $this->grabBlackCards($user, $game, $expansionIds);

        return $game;
    }

    public function grabWhiteCards($user, $game, $expansionIds)
    {
        $pickedCards = WhiteCard::whereIn('expansion_id', $expansionIds)
            ->inRandomOrder()->limit(Game::HAND_LIMIT)->get();

        $pickedCards->each(fn ($item) =>
            UserGameWhiteCards::create([
                'game_id' => $game->id,
                'user_id' => $user->id,
                'white_card_id' => $item->id
            ])
        );
    }

    public function grabBlackCards($user, $game, $expansionIds)
    {
        $pickedCard = BlackCard::whereIn('expansion_id', $expansionIds)
            ->inRandomOrder()->first();

        UserGameBlackCards::create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'black_card_id' => $pickedCard->id
        ]);
    }

    public function joinGame(Game $game, User $user)
    {
       return GameUser::create([
            'game_id' => $game->id,
            'user_id' => $user->id
        ]);
    }
}
