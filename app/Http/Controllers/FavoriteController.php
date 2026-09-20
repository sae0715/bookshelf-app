<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $books = Auth::user()->favoriteBooks()->paginate(10);

        return view('favorites.index', compact('books'));
    }

    public function toggle(Book $book): RedirectResponse
    {
        $result = Auth::user()->favoriteBooks()->toggle($book->id);

        $message = ! empty($result['attached'])
            ? 'お気に入りに追加しました。'
            : 'お気に入りを解除しました。';

        return back()->with('success', $message);
    }
}
