<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_userはbooksを取得できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->books->contains($book));
    }

    public function test_userはfavorite_booksを取得できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->favoriteBooks()->attach($book);

        $this->assertTrue($user->favoriteBooks->contains($book));
    }

    public function test_userはreviewsを取得できる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->reviews->contains($review));
    }

    public function test_userはliked_reviewsを取得できる(): void
    {
        $user = User::factory()->create();
        $review = Review::factory()->create();
        $user->likedReviews()->attach($review);

        $this->assertTrue($user->likedReviews->contains($review));
    }
}
