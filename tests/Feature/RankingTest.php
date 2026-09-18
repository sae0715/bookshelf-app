<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_レビュー平均評価の高い順に表示される(): void
    {
        $lowRatedBook = Book::factory()->create(['title' => '低評価本']);
        Review::factory()->create(['book_id' => $lowRatedBook->id, 'rating' => 2]);

        $highRatedBook = Book::factory()->create(['title' => '高評価本']);
        Review::factory()->create(['book_id' => $highRatedBook->id, 'rating' => 5]);

        $response = $this->get('/ranking');

        $response->assertSeeInOrder(['高評価本', '低評価本']);
    }

    public function test_レビューが無い書籍はランキングに表示されない(): void
    {
        Book::factory()->create(['title' => 'レビューなし本']);

        $response = $this->get('/ranking');

        $response->assertDontSee('レビューなし本');
    }
}
