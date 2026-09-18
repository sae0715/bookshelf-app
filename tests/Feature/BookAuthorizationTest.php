<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Favorite;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_本人は書籍を編集できる(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($owner)->get("/books/{$book->id}/edit");

        $response->assertStatus(200);
    }

    public function test_本人以外は書籍を編集できず403が返る(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get("/books/{$book->id}/edit");

        $response->assertStatus(403);
    }

    public function test_本人以外は書籍を削除できず403が返る(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete("/books/{$book->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('books', ['id' => $book->id]);
    }

    public function test_書籍削除時に関連するレビューといいねが削除される(): void
    {
        $owner = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $review = Review::factory()->create(['book_id' => $book->id]);

        $this->actingAs($owner)->delete("/books/{$book->id}");

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
