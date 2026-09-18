<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_ゲストは書籍一覧を閲覧できる(): void
    {
        $response = $this->get('/books');
        $response->assertStatus(200);
    }

    public function test_ゲストは書籍詳細を閲覧できる(): void
    {
        $book = Book::factory()->create();
        $response = $this->get("/books/{$book->id}");
        $response->assertStatus(200);
    }

    public function test_ゲストはランキングを閲覧できる(): void
    {
        $response = $this->get('/ranking');
        $response->assertStatus(200);
    }

    public function test_ゲストは書籍登録画面にアクセスするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get('/books/create');
        $response->assertRedirect('/login');
    }

    public function test_ゲストはジャンル一覧にアクセスするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get('/genres');
        $response->assertRedirect('/login');
    }

    public function test_ゲストはお気に入り一覧にアクセスするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get('/favorites');
        $response->assertRedirect('/login');
    }
}
