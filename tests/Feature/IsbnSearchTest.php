<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IsbnSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_13桁以外のISBNはバリデーションエラーになる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/books/isbn/12345');

        $response->assertStatus(400);
        $response->assertJson(['error' => 'ISBNは13桁で入力してください。']);
    }

    public function test_ISBN検索で書籍情報が取得できる(): void
    {
        $user = User::factory()->create();
        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'items' => [
                    [
                        'volumeInfo' => [
                            'title' => 'テスト書籍',
                            'authors' => ['テスト著者A', 'テスト著者B'],
                            'publishedDate' => '2020-01-01',
                            'description' => 'テスト説明文',
                            'imageLinks' => ['thumbnail' => 'https://example.com/image.jpg'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($user)->get('/books/isbn/9784101010014');

        $response->assertStatus(200);
        $response->assertJson([
            'title' => 'テスト書籍',
            'author' => 'テスト著者A、テスト著者B',
            'published_date' => '2020-01-01',
            'description' => 'テスト説明文',
            'image_url' => 'https://example.com/image.jpg',
        ]);
    }

    public function test_書籍が見つからない場合は404が返る(): void
    {
        $user = User::factory()->create();
        Http::fake([
            'www.googleapis.com/*' => Http::response(['items' => []], 200),
        ]);

        $response = $this->actingAs($user)->get('/books/isbn/9784101010014');

        $response->assertStatus(404);
        $response->assertJson(['error' => '書籍が見つかりませんでした。']);
    }

    public function test_クォータ超過時は429が返る(): void
    {
        $user = User::factory()->create();
        Http::fake([
            'www.googleapis.com/*' => Http::response([], 429),
        ]);

        $response = $this->actingAs($user)->get('/books/isbn/9784101010014');

        $response->assertStatus(429);
        $response->assertJson(['error' => 'Google Books API のクォータを超過しました。.env に GOOGLE_BOOKS_API_KEY を設定してください。']);
    }

    public function test_API通信エラー時は500が返る(): void
    {
        $user = User::factory()->create();
        Http::fake(function () {
            throw new ConnectionException('Connection error');
        });

        $response = $this->actingAs($user)->get('/books/isbn/9784101010014');

        $response->assertStatus(500);
        $response->assertJson(['error' => 'API通信エラーが発生しました。']);
    }

    public function test_ゲストはISBN検索にアクセスするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get('/books/isbn/9784101010014');

        $response->assertRedirect('/login');
    }
}
