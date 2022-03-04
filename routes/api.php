<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\BeerController;
use App\Http\Controllers\PubsController;
use App\Http\Controllers\QuestController;

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
	Route::get('/obtenerCervezas',[BeerController::class,'obtenerCervezas']);
	Route::put('/altaBeer',[BeerController::class,'altaBeer'])->withoutMiddleware(['login-api-token']);
	Route::get('/getUserProfile',[UsersController::class,'getUserProfile']);
	Route::post('/uploadProfileImage',[UsersController::class,'uploadProfileImage']);
	Route::get('/getRanking',[UsersController::class,'getRanking']);
	Route::get('/getUserPositionRanking',[UsersController::class,'getUserPositionRanking']);
	Route::post('/addBeerToFavourites',[UsersController::class,'addBeerToFavourites']);
	Route::get('/getFavouritesBeersFromUser',[UsersController::class,'getFavouritesBeersFromUser']);
	Route::get('/obtenerCervezasTiposMain',[BeerController::class,'obtenerCervezasTiposMain']);
	Route::get('/getPubs',[PubsController::class,'getPubs']);
	Route::get('/getPubsByName',[PubsController::class,'getPubsByName']);
	Route::get('/getQuests',[QuestController::class,'getQuests']);
	Route::post('/editUserData',[UsersController::class,'editUserData']);
});