<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['middleware' => 'auth'], function () {
    Route::resource('welcome', 'ItemsController');
});

Route::get('/', 'ItemsController@index');

Route::get('showToken', function () {
    echo csrf_token();
});

Route::get('/add', function () {
    if (Auth::check()) {
        return view('create');
    } else {
        return redirect('login');
    }
});

