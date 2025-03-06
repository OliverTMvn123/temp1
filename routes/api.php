<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OTPController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/generate-insert-commands', [OTPController::class, 'generateInsertCommands']);
Route::post('/generate-insert-commands1', [OTPController::class, 'generateInsertCommands1']);
Route::post('/generate-insert-commands2', [OTPController::class, 'generateInsertCommands2']);
