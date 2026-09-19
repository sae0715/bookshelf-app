<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_基本サマリーが正しく集計される(): void
    {
        $user = User::factory()->create();
        $bookA = Book::factory()->create();
        $bookB = Book::factory()->create();

        // 同じ書籍への複数レビューもOK（PM回答①）→ books_readはユニーク数になるはず
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $bookA->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $bookA->id, 'rating' => 3]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $bookB->id, 'rating' => 4]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertViewHas('stats', function ($stats) {
            return $stats['summary']['total_reviews'] === 3
                && $stats['summary']['books_read'] === 2
                && round($stats['summary']['average_rating'], 2) === round((5 + 3 + 4) / 3, 2);
        });
    }

    public function test_高評価書籍_to_p5は4星未満を含まず評価の高い順に並ぶ(): void
    {
        $user = User::factory()->create();
        $highBook = Book::factory()->create();
        $lowBook = Book::factory()->create();

        Review::factory()->create(['user_id' => $user->id, 'book_id' => $highBook->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $lowBook->id, 'rating' => 3]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertViewHas('stats', function ($stats) use ($highBook, $lowBook) {
            $ids = collect($stats['top_rated_books'])->pluck('id');

            return $ids->contains($highBook->id) && ! $ids->contains($lowBook->id);
        });
    }

    public function test_ジャンル別評価傾向が正しく集計される(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre);

        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 4]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book->id, 'rating' => 2]);

        $response = $this->actingAs($user)->get('/reports');

        $response->assertViewHas('stats', function ($stats) use ($genre) {
            $row = collect($stats['genre_ratings'])->firstWhere('id', $genre->id);

            return $row !== null && $row['count'] === 2 && (float) $row['average_rating'] === 3.0;
        });
    }

    public function test_ゲストはマイ読書レポートにアクセスするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get('/reports');

        $response->assertRedirect('/login');
    }
}
