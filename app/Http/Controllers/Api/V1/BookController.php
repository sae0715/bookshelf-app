<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Http\Requests\BookRequest;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with('genres')->withCount('reviews')->withAvg('reviews', 'rating');

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('author', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('genre_id')) {
            $query->whereHas('genres', fn($q) => $q->where('genres.id', $request->genre_id));
        }

        return response()->json($query->paginate(20));
    }

    public function show(Book $book)
    {
        $book->load(['genres', 'reviews.user']);

        return response()->json($book);
    }

    public function store(BookRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = \App\Models\User::first()->id;

        $book = Book::create($data);
        $book->genres()->sync($request->genres);

        return response()->json($book, 201);
    }

    public function update(BookRequest $request, Book $book)
    {
        $book->update($request->validated());
        $book->genres()->sync($request->genres);

        return response()->json($book, 200);
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return response()->json(null, 204);
    }
}
