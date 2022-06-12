<?php

use Illuminate\Http\Request;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/callback', \App\Http\Controllers\CallbackController::class);
Route::post('/getSettingsView', [\App\Http\Controllers\SettingsController::class, 'getView']);
Route::get('/getToken', [\App\Http\Controllers\SettingsController::class, 'getToken']);
Route::post('/saveSettings', [\App\Http\Controllers\SettingsController::class, 'saveSettings']);
