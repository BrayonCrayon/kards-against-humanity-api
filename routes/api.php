<?php

use App\Http\Controllers\Game\CreateGameController;
use App\Http\Controllers\Game\DrawWhiteCardsController;
use App\Http\Controllers\Game\GetExpansionsController;
use App\Http\Controllers\Game\GetGameStateController;
use App\Http\Controllers\Game\JoinGameController;
use App\Http\Controllers\Game\KickPlayerController;
use App\Http\Controllers\Game\LeaveGameController;
use App\Http\Controllers\Game\RedrawController;
use App\Http\Controllers\Game\RotateGameController;
use App\Http\Controllers\Game\Round\GetRoundWinnerController;
use App\Http\Controllers\Game\SpectateGameController;
use App\Http\Controllers\Game\Spectator\GetSpectateDataController;
use App\Http\Controllers\Game\StoreRoundWinnerController;
use App\Http\Controllers\Game\SelectCardsController;
use App\Http\Controllers\Game\SubmittedCardsController;
use App\Http\Controllers\PlayersController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Broadcast::routes(['middleware' => ['auth:sanctum']]);

/*
 *****************************
 **** Game Routes         ****
 *****************************
 */
Route::post('/game', CreateGameController::class)->name('game.store');
Route::post('/game/{game:code}/join', JoinGameController::class)->name('game.join');
Route::post('/game/{game:code}/spectate', SpectateGameController::class)->name('game.spectate');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/game/{game}/leave', LeaveGameController::class)->name('game.leave');
    Route::get('/game/{game}/submitted-cards', SubmittedCardsController::class)->name('game.submitted.cards');
    Route::post('/game/{game}/select', SelectCardsController::class)->name('game.select');
    Route::post('/game/{game}/rotate', RotateGameController::class)->name('game.rotate');
    Route::get('/game/{game}/whiteCards/draw', DrawWhiteCardsController::class)->name('game.whiteCards.draw');
    Route::get('/game/{game}', GetGameStateController::class)->name('game.show');
    Route::get('/game/{game}/spectate', GetSpectateDataController::class)->name('game.spectate.show');
    Route::post('/game/{game}/winner', StoreRoundWinnerController::class)->name('game.winner');
    Route::get('/game/{game}/round/winner/{blackCard}', GetRoundWinnerController::class)->name('game.round.winner');
    Route::post('/game/{game}/player/{user}/kick', KickPlayerController::class)->name('game.player.kick');
    Route::post('/game/{game}/redraw', RedrawController::class)->name('game.redraw');
    Route::get('/game/{game}/players', PlayersController::class)->name('game.players.index');
});

Route::get('/expansions', GetExpansionsController::class)->name('expansions.index');
