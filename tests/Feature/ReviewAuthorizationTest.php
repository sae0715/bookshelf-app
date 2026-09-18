<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーはレビューを投稿できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'rating' => 5,
            'comment' => '最高でした',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('reviews', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_rating未指定だとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post("/books/{$book->id}/reviews", [
            'comment' => 'コメントだけ',
        ]);

        $response->assertSessionHasErrors('rating');
    }

    public function test_投稿者本人はレビューを編集できる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("/reviews/{$review->id}/edit");

        $response->assertStatus(200);
    }

    public function test_本人以外はレビューを編集できず403が返る(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->get("/reviews/{$review->id}/edit");

        $response->assertStatus(403);
    }

    public function test_本人以外はレビューを削除できず403が返る(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($otherUser)->delete("/reviews/{$review->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);
    }
}
