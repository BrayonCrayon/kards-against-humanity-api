<?php

namespace Tests\Traits;

use App\Models\BlackCard;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\RoundWinner;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserGameWhiteCard;
use App\Services\GameService;

trait GameUtilities
{

    public function createGame(int $usersCount = 1, int $blackCardCount = 1, int $whiteCardCount = null, int $blackCardPick = 1): Game
    {
        $gameService = new GameService();
        $game = Game::factory()
            ->has(Expansion::factory()
                ->hasWhiteCards($whiteCardCount ?? ($usersCount + 1) * Game::HAND_LIMIT)
                ->has(BlackCard::factory($blackCardCount)->state([
                'pick' => $blackCardPick
            ])))
            ->hasUsers($usersCount)
            ->create();

        Setting::factory()->for($game)->create();
        $this->drawBlackCard($game);

        $game->players->each(fn ($user) => $gameService->drawWhiteCards($user,$game));

        return $game;
    }

    public function drawCardsForUser(User $user, Game $game): void
    {
        $game->expansions->load(['whiteCards'])
            ->pluck('whiteCards')
            ->flatten()
            ->filter(function($card) {
                return UserGameWhiteCard::where('white_card_id', $card->id)->doesntExist();
            })
            ->take(Game::HAND_LIMIT - $user->hand->count())
            ->each(function ($card) use ($user, $game) {
                UserGameWhiteCard::factory()
                    ->create([
                        'white_card_id' => $card->id,
                        'game_id' => $game->id,
                        'user_id' => $user->id,
                    ]);
            });
    }

    public function drawBlackCard(Game $game): void
    {
        $game->blackCards()->attach($game->expansions()->first()->blackCards()->first());
    }

    public function selectAllPlayersCards($game): void
    {
        $game->nonJudgeUsers()->each(fn($user) => $this->selectCardsForUser($user, $game));
    }

    public function selectAndSubmitPlayerForRoundWinner(User $user, Game $game) : void
    {
        $this->selectCardsForUser($user, $game);
        $this->submitPlayerForRoundWinner($user, $game);
    }

    public function selectCardsForUser(User $user, Game $game) : void
    {
        $user->hand()
            ->take($game->blackCardPick)
            ->update([
                'selected' => true
            ]);
    }

    public function submitPlayerForRoundWinner(User $user, Game $game) : void
    {
        $user->hand()
            ->whereSelected(true)
            ->each(function (UserGameWhiteCard $card) use ($game, $user) {
                RoundWinner::factory()
                    ->for($user)
                    ->for($game)
                    ->for($card->whiteCard)
                    ->for($game->blackCard)
                    ->create();
            });
    }
}
