<?php

use App\Http\Controllers\Game\CreateGameController;
use App\Http\Controllers\Game\DrawWhiteCardsController;
use App\Http\Controllers\Game\GetExpansionsController;
use App\Http\Controllers\Game\GetGameStateController;
use App\Http\Controllers\Game\JoinGameController;
use App\Http\Controllers\Game\RotateGameController;
use App\Http\Controllers\Game\StoreGameWinnerController;
use App\Http\Controllers\Game\SubmitCardsController;
use App\Http\Controllers\Game\SubmittedCardsController;
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/game/{game}/submitted-cards', SubmittedCardsController::class)->name('game.submitted.cards');
    Route::post('/game/{game}/submit', SubmitCardsController::class)->name('game.submit');
    Route::post('/game/{game}/rotate', RotateGameController::class)->name('game.rotate');
    Route::get('/game/{game}/whiteCards/draw', DrawWhiteCardsController::class)->name('game.whiteCards.draw');
    Route::get('/game/{game}', GetGameStateController::class)->name('game.show');
    Route::post('/game/{game}/winner', StoreGameWinnerController::class)->name('game.winner');
});

Route::get('/expansions', GetExpansionsController::class)->name('expansions.index');
