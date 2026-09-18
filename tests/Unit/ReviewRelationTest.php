<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_reviewはliked_by_usersでいいねしたユーザーを取得できる(): void
    {
        $review = Review::factory()->create();
        $user = User::factory()->create();
        $user->likedReviews()->attach($review);

        $this->assertTrue($review->likedByUsers->contains($user));
    }

    public function test_reviewはuserを取得できる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($review->user->is($user));
    }

    public function test_reviewはbookを取得できる(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $this->assertTrue($review->book->is($book));
    }
}
