<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RessourceController;

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

Route::post('auth', [UserController::class, 'authenticate']);
Route::post('user', [UserController::class, 'register']);
Route::get('user/{id}/file', [RessourceController::class, 'show']);

Route::group(['middleware' => ['jwt.verify']], function () {
    Route::get('logout', [UserController::class, 'logout']);
    Route::post('user/{id}/file', [RessourceController::class, 'upload']);
    Route::put('user/{id}',  [UserController::class, 'updateUser']);
    Route::put('file/{id}',  [RessourceController::class, 'update']);
    Route::delete('user/{id}',  [UserController::class, 'deleteUser']);
    Route::delete('file/{id}',  [RessourceController::class, 'destroy']);
});
