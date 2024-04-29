<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;

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

Route::middleware('api')->post('/transacao',[TransactionController::class, 'store']);
Route::post('/conta', 'App\Http\Controllers\AccountController@register');
Route::get('/conta', 'App\Http\Controllers\AccountController@getByAccountId');
Route::post('/login', 'App\Http\Controllers\AccountController@login');
Route::delete('/users/{id}', 'App\Http\Controllers\AccountController@destroy');
