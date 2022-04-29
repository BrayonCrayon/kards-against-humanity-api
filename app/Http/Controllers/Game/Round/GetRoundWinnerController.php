<?php

namespace App\Http\Controllers\Game\Round;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserGameWhiteCardResource;
use App\Models\BlackCard;
use App\Models\Game;
use App\Services\GameService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetRoundWinnerController extends Controller
{
    public function __construct(private GameService $gameService)
    {
    }

    /**
     * @throws AuthorizationException
     */
    public function __invoke(Request $request, Game $game, BlackCard $blackCard): JsonResponse
    {
        $this->authorize('get', $game);

        $winnerData = $this->gameService->roundWinner($game, $blackCard);

        return response()->json([
            'data' => [
                'user_id' => $winnerData['user']->id,
                'submitted_cards' => UserGameWhiteCardResource::collection($winnerData['userGameWhiteCards']),
                'black_card' => $blackCard
            ]
        ]);
    }
}
