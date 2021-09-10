<?php


namespace App\Services;


use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameBlackCards;
use App\Models\GameUser;
use App\Models\User;
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

    public function createGame($user, $expansionIds)
    {
        $game = Game::create([
            'name' => $this->generator->getName(),
            'judge_id' => $user->id,
        ]);

        $game->users()->save($user);

        $game->expansions()->saveMany(Expansion::idsIn($expansionIds)->get());

        $this->drawWhiteCards($user, $game);
        $this->drawBlackCard($game);

        return $game;
    }

    public function drawWhiteCards($user, $game)
    {
        $drawLimit = Game::HAND_LIMIT - $user->whiteCards()->count();
        $pickedCards = WhiteCard::whereIn('expansion_id', $game->expansions->pluck('id')->toArray())
            ->whereNotIn('id', function ($query) use ($game) {
                $query->select('white_card_id')->from('user_game_white_cards')->whereGameId($game->id);
            })
            ->inRandomOrder()
            ->limit($drawLimit)
            ->get();

        $pickedCards->each(fn($item) => UserGameWhiteCards::create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'white_card_id' => $item->id
        ]));

        return $pickedCards;
    }

    public function drawBlackCard($game)
    {
        $drawnCards = $game->gameBlackCards()->onlyTrashed()->get();
        $pickedCard = BlackCard::whereIn('expansion_id', $game->expansions->pluck('id'))
            ->whereNotIn('id', $drawnCards->pluck('id'))
            ->inRandomOrder()
            ->firstOrFail();

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
        UserGameWhiteCards::where('game_id', $game->id)
            ->where('user_id', auth()->id())
            ->whereIn('white_card_id', $whiteCardIds)
            ->update([
                'selected' => true
            ]);
    }

    public function discardBlackCard($game)
    {
        $game->gameBlackCards()->firstOrFail()->delete();
    }

    public function discardWhiteCards($game)
    {
        UserGameWhiteCards::whereGameId($game->id)->where('selected', true)->delete();
    }

    public function updateJudge($game, $judgeId)
    {
        $game->update([
            'judge_id' => $judgeId,
        ]);
    }
}
