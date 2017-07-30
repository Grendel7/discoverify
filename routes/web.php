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

Route::get('/', function () {
    return view('welcome');
});

Route::get('login/spotify', 'Auth\LoginController@redirectToProvider');
Route::get('login/spotify/callback', 'Auth\LoginController@handleProviderCallback');
Auth::routes();

Route::middleware(['auth'])->group(function () {
    Route::resource('channels', 'ChannelsController');
});