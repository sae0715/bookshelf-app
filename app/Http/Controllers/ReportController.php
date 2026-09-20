<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Display the authenticated user's reading report.
     */
    public function index(): View
    {
        $userId = Auth::id();

        return view('reports.index', [
            'stats' => [
                'summary' => $this->buildSummary($userId),
                'rating_distribution' => $this->buildRatingDistribution($userId),
                'top_rated_books' => $this->buildTopRatedBooks($userId),
                'genre_ratings' => $this->buildGenreRatings($userId),
            ],
        ]);
    }

    /**
     * 基本サマリー：総レビュー数、読了冊数（レビュー投稿済みのユニーク書籍数）、平均評価点。
     * 読了冊数の定義はPM回答⑤（レビューを投稿した書籍のユニーク数）に準拠。
     */
    private function buildSummary(int $userId): array
    {
        $reviews = Review::where('user_id', $userId);

        return [
            'total_reviews' => (clone $reviews)->count(),
            'books_read' => (clone $reviews)->distinct('book_id')->count('book_id'),
            'average_rating' => (float) (clone $reviews)->avg('rating'),
        ];
    }

    /**
     * 評価分布：1〜5星ごとの件数（インデックス0が★1件数、4が★5件数）。
     */
    private function buildRatingDistribution(int $userId): Collection
    {
        $counts = Review::where('user_id', $userId)
            ->select('rating', DB::raw('COUNT(*) as count'))
            ->groupBy('rating')
            ->pluck('count', 'rating');

        return collect(range(1, 5))->map(fn ($rating) => $counts->get($rating, 0));
    }

    /**
     * 高評価書籍TOP5：4星以上の書籍を評価の高い順に最大5件（同一書籍は最高評価でまとめる）。
     */
    private function buildTopRatedBooks(int $userId): Collection
    {
        $topRatings = Review::where('user_id', $userId)
            ->where('rating', '>=', 4)
            ->select('book_id', DB::raw('MAX(rating) as rating'))
            ->groupBy('book_id')
            ->orderByDesc('rating')
            ->limit(5)
            ->get();

        $books = Book::whereIn('id', $topRatings->pluck('book_id'))->get()->keyBy('id');

        return $topRatings->map(fn ($row) => [
            'id' => $row->book_id,
            'title' => $books[$row->book_id]->title,
            'author' => $books[$row->book_id]->author,
            'rating' => (int) $row->rating,
        ])->values();
    }

    /**
     * ジャンル別評価傾向TOP5：ジャンルごとの平均評価と件数を高い順に最大5件。
     * 1レビューが複数ジャンルに紐づく書籍のレビューの場合、各ジャンルの集計にそれぞれ加算される。
     */
    private function buildGenreRatings(int $userId): Collection
    {
        return DB::table('reviews')
            ->join('books', 'books.id', '=', 'reviews.book_id')
            ->join('book_genre', 'book_genre.book_id', '=', 'books.id')
            ->join('genres', 'genres.id', '=', 'book_genre.genre_id')
            ->where('reviews.user_id', $userId)
            ->select(
                'genres.id',
                'genres.name',
                DB::raw('AVG(reviews.rating) as average_rating'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('genres.id', 'genres.name')
            ->orderByDesc('average_rating')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'average_rating' => (float) $row->average_rating,
                'count' => (int) $row->count,
            ]);
    }
}
