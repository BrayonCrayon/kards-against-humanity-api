<?php


namespace App\Services;


use App\Http\Requests\SubmitCardRequest;
use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\User;
use App\Models\GameBlackCards;
use App\Models\UserGameWhiteCards;
use App\Models\WhiteCard;
use Illuminate\Support\Facades\Auth;
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
            'name' => $this->generator->getName(),
            'judge_id' => $user->id,
        ]);

        $game->users()->save($user);

        $game->expansions()->saveMany(Expansion::idsIn($expansionIds)->get());

        $this->grabWhiteCards($user, $game, $expansionIds);
        $this->drawBlackCard($game, $expansionIds);

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

    public function drawBlackCard($game)
    {
        $drawnCards = $game->gameBlackCards()->onlyTrashed()->get();
        $pickedCard = BlackCard::whereIn('expansion_id', $game->expansions->pluck('id'))
            ->whereNotIn('id', $drawnCards->pluck('id'))
            ->inRandomOrder()->first();

        GameBlackCards::create([
            'game_id' => $game->id,
            'black_card_id' => $pickedCard->id
        ]);

        return $pickedCard;
    }

    public function joinGame(Game $game, User $user)
    {
       return GameUser::create([
            'game_id' => $game->id,
            'user_id' => $user->id
        ]);
    }

    public function submitCards($whiteCardIds, Game $game)
    {
        $user = Auth::user();

        $cardsToSelect = UserGameWhiteCards::where('game_id', $game->id)
            ->where('user_id', $user->id)
            ->whereIn('white_card_id', $whiteCardIds)
            ->get();

        $cardsToSelect->each(function ($card) {
            $card->selected = true;
            $card->save();
        });
    }

    public function discardBlackCard($game)
    {
        $game->gameBlackCards()->first()->delete();
    }
}
