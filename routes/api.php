<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

Route::post('login', 'APILoginController@login');
Route::post('register', 'ApiController@register');

Route::group(['middleware' => 'jwt.auth:api'], function () {
        Route::delete('/delete/{id}', 'ItemsController@destroy')->middleware('checkAdmin');
        Route::post('/edit/{id}', 'ItemsController@update')->middleware('checkAdmin');
});
Route::post('/add', 'ItemsController@store')->middleware('jwt.auth:api');

Route::middleware('jwt.auth:api')->get('users', function () {
    return auth('api')->user();
});
