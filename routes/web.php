<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\RankingController;
use App\Http\Controllers\ReviewController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IsbnSearchController;

Route::get('/', [BookController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/books/isbn/{isbn}', [IsbnSearchController::class, 'show'])->name('books.isbn-search');

    Route::resource('books', BookController::class)->except(['index', 'show']);

    Route::post('/books/{book}/reviews', [ReviewController::class, 'store'])->name('reviews.store');
    Route::get('/reviews/{review}/edit', [ReviewController::class, 'edit'])->name('reviews.edit');
    Route::put('/reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
    Route::post('/reviews/{review}/like', [ReviewController::class, 'toggleLike'])->name('reviews.like');

    Route::resource('genres', GenreController::class);

    Route::get('/favorites', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favorites/{book}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    Route::get('/reading-plans', fn() => '読書計画機能は準備中です')->name('reading-plans.index');
    Route::get('/reports', fn() => 'レポート機能は準備中です')->name('reports.index');
    Route::get('/notifications', fn() => '通知機能は準備中です')->name('notifications.index');
});

Route::resource('books', BookController::class)->only(['index', 'show']);

Route::get('/ranking', [RankingController::class, 'index'])->name('ranking.index');
