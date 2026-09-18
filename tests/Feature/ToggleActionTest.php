<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToggleActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_お気に入りに追加できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->actingAs($user)->post("/favorites/{$book->id}");

        $this->assertDatabaseHas('favorites', ['user_id' => $user->id, 'book_id' => $book->id]);
    }

    public function test_お気に入りを解除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->favoriteBooks()->attach($book);

        $this->actingAs($user)->post("/favorites/{$book->id}");

        $this->assertDatabaseMissing('favorites', ['user_id' => $user->id, 'book_id' => $book->id]);
    }

    public function test_レビューにいいねできる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();

        $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $this->assertDatabaseHas('review_likes', ['user_id' => $user->id, 'review_id' => $review->id]);
    }

    public function test_いいねを解除できる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();
        $user->likedReviews()->attach($review);

        $this->actingAs($user)->post("/reviews/{$review->id}/like");

        $this->assertDatabaseMissing('review_likes', ['user_id' => $user->id, 'review_id' => $review->id]);
    }
}
