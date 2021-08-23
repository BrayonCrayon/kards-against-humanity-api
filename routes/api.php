<?php

use App\Http\Controllers\Game\CreateGameController;
use App\Http\Controllers\Game\JoinGameController;
use App\Http\Controllers\Game\RotateGameController;
use App\Http\Controllers\Game\SubmitCardsController;
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
Route::post('/game/store', CreateGameController::class)->name('game.store');

Route::post('/game/join/{game}', JoinGameController::class)->name('game.join');

Route::post('/game/submit/{game}', SubmitCardsController::class)->name('game.submit');

Route::post('/game/rotate/{game}', RotateGameController::class)->name('game.rotate');
