<?php


namespace App\Services;


use App\Events\GameJoined;
use App\Events\GameRotation;
use App\Events\WinnerSelected;
use App\Http\Resources\UserGameWhiteCardResource;
use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\RoundWinner;
use App\Models\User;
use App\Models\UserGameWhiteCard;
use App\Models\WhiteCard;
use Facades\App\Services\HelperService;
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
            'code' => HelperService::generateCode("#?#?"),
            'redraw_limit' => 2,
        ]);

        $game->users()->save($user);

        $game->expansions()->saveMany(Expansion::idsIn($expansionIds)->get());

        $this->drawWhiteCards($user, $game);
        $this->drawBlackCard($game);

        return $game;
    }

    public function drawWhiteCards($user, $game)
    {
        $drawLimit = Game::HAND_LIMIT - $user->hand()->count();
        $pickedCards = WhiteCard::whereIn('expansion_id', $game->expansions->pluck('id')->toArray())
            ->whereNotIn('id', function ($query) use ($game) {
                $query->select('white_card_id')->from('user_game_white_cards')->whereGameId($game->id);
            })
            ->inRandomOrder()
            ->limit($drawLimit)
            ->get();

        $pickedCards->each(fn($item) => UserGameWhiteCard::create([
            'game_id' => $game->id,
            'user_id' => $user->id,
            'white_card_id' => $item->id
        ]));

        return $pickedCards;
    }

    public function drawBlackCard($game)
    {
        $drawnCards = $game->deletedBlackCards()->get();
        $pickedCard = BlackCard::whereIn('expansion_id', $game->expansions->pluck('id'))
            ->whereNotIn('id', $drawnCards->pluck('id'))
            ->inRandomOrder()
            ->firstOrFail();

        $game->blackCards()->attach($pickedCard);

        return $pickedCard;
    }

    public function joinGame(Game $game, User $user)
    {
        $joinedUser = GameUser::create([
            'game_id' => $game->id,
            'user_id' => $user->id
        ]);

        event(new GameJoined($game, $user));

        return $joinedUser;
    }

    public function selectCards($whiteCardIds, Game $game, User $user) : void
    {
        $cardOrder = 1;
        collect($whiteCardIds)->each(function ($id) use(&$cardOrder, $user, $game) {
            UserGameWhiteCard::where('game_id', $game->id)
                ->where('user_id', $user->id)
                ->where('white_card_id', $id)
                ->update([
                    'selected' => true,
                    'order' => $cardOrder++
                ]);
        });
    }

    public function discardBlackCard($game)
    {
        $game->gameBlackCards()->firstOrFail()->delete();
    }

    public function discardWhiteCards($game)
    {
        UserGameWhiteCard::whereGameId($game->id)->where('selected', true)->delete();
    }

    public function updateJudge($game, $judgeId)
    {
        $game->update([
            'judge_id' => $judgeId,
        ]);
    }

    public function selectWinner(Game $game, $user)
    {
        $user->hand()->whereSelected(true)->get()->each(function ($item) use ($game, $user) {
            RoundWinner::create([
                'game_id' => $game->id,
                'user_id' => $user->id,
                'white_card_id' => $item->white_card_id,
                'black_card_id' => $game->blackCard->id,
            ]);
        });
        event(new WinnerSelected($game, $user));
    }

    public function getSubmittedCards(Game $game)
    {
        return $game->nonJudgeUsers()->get()->map(function($user) {
            return [
                'user_id' => $user->id,
                'submitted_cards' => UserGameWhiteCardResource::collection($user->hand()->whereSelected(true)->get()),
            ];
        })->shuffle();
    }

    public function roundWinner(Game $game, BlackCard $blackCard)
    {
        $winner = RoundWinner::whereGameId($game->id)
            ->whereBlackCardId($blackCard->id)
            ->get();

        return [
            "user" => $winner->first()->user,
            "userGameWhiteCards" => UserGameWhiteCard::withTrashed()
                ->whereUserId($winner->first()->user->id)
                ->whereGameId($game->id)
                ->whereIn('white_card_id', $winner->pluck('white_card_id'))
                ->get()
        ];
    }

    public function resetDrawCount(Game $game) {
        GameUser::whereGameId($game->id)
            ->update([
                'redraw_count' => 0
            ]);
    }

    public function incrementDrawCount(Game $game, User $user) {
        GameUser::whereGameId($game->id)
            ->whereUserId($user->id)
            ->increment('redraw_count');
    }

    public function rotateGame(Game $game)
    {
        $userIds = $game->players()->pluck('users.id');

        $currentJudgeIndex = $userIds->search($game->judge_id);
        $nextJudgeIndex = ($currentJudgeIndex + 1) % $userIds->count();

        $this->discardWhiteCards($game);
        $this->discardBlackCard($game);
        $this->drawBlackCard($game);
        $this->resetDrawCount($game);

        $game->nonJudgeUsers->each(function($user) use ($game) {
            $this->drawWhiteCards($user, $game);
        });

        $this->updateJudge($game, $userIds[$nextJudgeIndex]);

        event(new GameRotation($game));
    }
}
