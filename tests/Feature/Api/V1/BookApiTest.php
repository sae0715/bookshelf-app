<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_書籍一覧が取得できる(): void
    {
        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/books');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_書籍詳細が取得できる(): void
    {
        $book = Book::factory()->create();

        $response = $this->getJson("/api/v1/books/{$book->id}");

        $response->assertStatus(200);
        $response->assertJson(['data' => ['id' => $book->id]]);
    }

    public function test_存在しない書籍を取得すると404が返る(): void
    {
        $response = $this->getJson('/api/v1/books/9999');

        $response->assertStatus(404);
    }

    public function test_書籍を登録できる(): void
    {
        $user = \App\Models\User::factory()->create();
        $genre = \App\Models\Genre::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', [
            'title' => 'API書籍',
            'author' => 'API著者',
            'isbn' => '9999999999999',
            'published_date' => '2020-01-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('books', ['title' => 'API書籍']);
    }

    public function test_バリデーションエラー時422が返る(): void
    {
        $user = \App\Models\User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', []);

        $response->assertStatus(422);
    }

    public function test_書籍を削除できる(): void
    {
        $user = \App\Models\User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }
}
