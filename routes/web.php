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

Route::get('login/spotify', 'Auth\LoginController@redirectToProvider')->name('login.spotify');
Route::get('login/spotify/callback', 'Auth\LoginController@handleProviderCallback');
Route::get('login', 'Auth\LoginController@showLoginForm')->name('login');
Route::post('logout', 'Auth\LoginController@logout')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::resource('tracks', 'TracksController');
    Route::resource('userChannels', 'UserChannelsController');
    Route::resource('playlists', 'PlaylistsController');
});