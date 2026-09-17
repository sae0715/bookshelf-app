<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::resource('books', BookController::class);
Route::get('/ranking', fn() => 'ランキング機能は準備中です')->name('ranking.index');
Route::get('/favorites', fn() => 'お気に入り機能は準備中です')->name('favorites.index');
Route::get('/genres', fn() => 'ジャンル機能は準備中です')->name('genres.index');
