<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_認証済みユーザーは読書計画を登録できる(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_同じ書籍に進行中の計画がすでにある場合は登録できない(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => ReadingPlanStatus::InProgress,
        ]);

        $response = $this->actingAs($user)->post('/reading-plans', [
            'book_id' => $book->id,
            'target_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertSessionHasErrors('book_id');
        $this->assertDatabaseCount('reading_plans', 1);
    }

    public function test_期限切れの計画に未来の期日を設定すると進行中に戻る(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Expired,
            'target_date' => now()->subDays(3),
        ]);

        $this->actingAs($user)->put("/reading-plans/{$plan->id}", [
            'target_date' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::InProgress->value,
        ]);
    }

    public function test_状態で絞り込むと該当する計画のみ表示される(): void
    {
        $user = User::factory()->create();
        ReadingPlan::factory()->create(['user_id' => $user->id, 'status' => ReadingPlanStatus::InProgress]);
        ReadingPlan::factory()->create(['user_id' => $user->id, 'status' => ReadingPlanStatus::Completed]);

        $response = $this->actingAs($user)->get('/reading-plans?status=completed');

        $response->assertViewHas('readingPlans', function ($readingPlans) {
            return $readingPlans->count() === 1
                && $readingPlans->first()->status === ReadingPlanStatus::Completed;
        });
    }

    public function test_ゲストは読書計画一覧にアクセスするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get('/reading-plans');

        $response->assertRedirect('/login');
    }
}
