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

Route::middleware(['login-api-token']) -> prefix('usuarios') -> group(function(){

	Route::put('/register',[UsersController::class,'register'])->withoutMiddleware(['login-api-token']);
	Route::post('/login',[UsersController::class,'login'])->withoutMiddleware(['login-api-token']);
	Route::post('/recoverPass',[UsersController::class,'recoverPass'])->withoutMiddleware(['login-api-token']);
});