<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーはジャンルを登録できる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/genres', ['name' => '新ジャンル']);

        $response->assertRedirect();
        $this->assertDatabaseHas('genres', ['name' => '新ジャンル']);
    }

    public function test_紐付く書籍が無いジャンルは削除できる(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    public function test_紐付く書籍があるジャンルは削除できない(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre);

        $response = $this->actingAs($user)->delete("/genres/{$genre->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('genres', ['id' => $genre->id]);
    }
}
