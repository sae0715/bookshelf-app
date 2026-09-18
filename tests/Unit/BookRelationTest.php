<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_bookはgenresを取得できる(): void
    {
        $book = Book::factory()->create();
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre);

        $this->assertTrue($book->genres->contains($genre));
    }

    public function test_bookはreviewsを取得できる(): void
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create(['book_id' => $book->id]);

        $this->assertTrue($book->reviews->contains($review));
    }

    public function test_bookはfavorited_byでお気に入りしたユーザーを取得できる(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();
        $user->favoriteBooks()->attach($book);

        $this->assertTrue($book->favoritedBy->contains($user));
    }

    public function test_bookはuserを取得できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($book->user->is($user));
    }
}
