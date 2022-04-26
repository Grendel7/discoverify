<?php

use Illuminate\Support\Facades\Route;

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


Route::get('login/spotify', [\App\Http\Controllers\Auth\LoginController::class, 'redirectToProvider'])->name('login.spotify');
Route::get('login/spotify/callback', [\App\Http\Controllers\Auth\LoginController::class, 'handleProviderCallback'])->name('login.spotify.callback');
Route::get('login', [\App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('logout', [\App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::resource('tracks', \App\Http\Controllers\TracksController::class);
    Route::resource('userChannels', \App\Http\Controllers\UserChannelsController::class);
    Route::resource('playlists', \App\Http\Controllers\PlaylistsController::class);
});
