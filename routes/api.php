<?php

use App\Http\Controllers\Game\CreateGameController;
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
