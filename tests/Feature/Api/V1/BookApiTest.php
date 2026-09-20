<?php

namespace Tests\Feature\Api\V1;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
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
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', [
            'title' => 'API書籍',
            'author' => 'API著者',
            'isbn' => '9999999999999',
            'published_date' => '2020-01-01',
            'genres' => [$genre->id],
            'user_id' => $user->id,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('books', ['title' => 'API書籍', 'user_id' => $user->id]);
    }

    public function test_バリデーションエラー時422が返る(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/books', []);

        $response->assertStatus(422);
    }

    public function test_書籍を削除できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    public function test_本人以外が書籍を更新しようとすると日本語メッセージ付きで403が返る(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['user_id' => $owner->id]);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($otherUser, 'sanctum')->putJson("/api/v1/books/{$book->id}", [
            'title' => '書き換えテスト',
            'author' => '書き換えテスト著者',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'この操作を実行する権限がありません。']);
    }
}
