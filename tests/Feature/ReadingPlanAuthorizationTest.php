<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_本人は進行中の計画を編集できる(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id, 'status' => ReadingPlanStatus::InProgress]);

        $response = $this->actingAs($user)->get("/reading-plans/{$plan->id}/edit");

        $response->assertStatus(200);
    }

    public function test_本人以外は計画を編集できず403が返る(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $owner->id, 'status' => ReadingPlanStatus::InProgress]);

        $response = $this->actingAs($otherUser)->get("/reading-plans/{$plan->id}/edit");

        $response->assertStatus(403);
    }

    public function test_完了済みの計画は本人でも編集できず403が返る(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id, 'status' => ReadingPlanStatus::Completed]);

        $response = $this->actingAs($user)->get("/reading-plans/{$plan->id}/edit");

        $response->assertStatus(403);
    }

    public function test_完了済みの計画は本人なら削除できる(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id, 'status' => ReadingPlanStatus::Completed]);

        $response = $this->actingAs($user)->delete("/reading-plans/{$plan->id}");

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseMissing('reading_plans', ['id' => $plan->id]);
    }

    public function test_本人は進行中の計画を読了にできる(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id, 'status' => ReadingPlanStatus::InProgress]);

        $this->actingAs($user)->post("/reading-plans/{$plan->id}/complete");

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::Completed->value,
        ]);
        $this->assertNotNull($plan->fresh()->completed_at);
    }

    public function test_既に完了済みの計画を再度読了にしようとすると403が返る(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id, 'status' => ReadingPlanStatus::Completed]);

        $response = $this->actingAs($user)->post("/reading-plans/{$plan->id}/complete");

        $response->assertStatus(403);
    }

    public function test_本人は完了済みの計画の読了を取り消せる(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post("/reading-plans/{$plan->id}/uncomplete");

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::InProgress->value,
            'completed_at' => null,
        ]);
    }

    public function test_進行中の計画の読了取り消しは403が返る(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id, 'status' => ReadingPlanStatus::InProgress]);

        $response = $this->actingAs($user)->post("/reading-plans/{$plan->id}/uncomplete");

        $response->assertStatus(403);
    }

    public function test_本人以外は完了済みの計画の読了を取り消せず403が返る(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $owner->id, 'status' => ReadingPlanStatus::Completed]);

        $response = $this->actingAs($otherUser)->post("/reading-plans/{$plan->id}/uncomplete");

        $response->assertStatus(403);
    }
}
