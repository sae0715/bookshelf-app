<?php

namespace Tests\Feature;

use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_自分の通知一覧を閲覧できる(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);
        $user->notify(new ReadingPlanReminder($plan, 'on_due_date'));

        $response = $this->actingAs($user)->get('/notifications');

        $response->assertStatus(200);
        $response->assertViewHas('notifications', fn ($notifications) => $notifications->count() === 1);
    }

    public function test_通知を既読にできる(): void
    {
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $user->id]);
        $user->notify(new ReadingPlanReminder($plan, 'on_due_date'));
        $notification = $user->notifications()->first();

        $response = $this->actingAs($user)->post("/notifications/{$notification->id}/read");

        $response->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_本人以外は他人の通知を既読にできず404が返る(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $plan = ReadingPlan::factory()->create(['user_id' => $owner->id]);
        $owner->notify(new ReadingPlanReminder($plan, 'on_due_date'));
        $notification = $owner->notifications()->first();

        $response = $this->actingAs($otherUser)->post("/notifications/{$notification->id}/read");

        $response->assertStatus(404);
    }

    public function test_ゲストは通知一覧にアクセスするとログイン画面にリダイレクトされる(): void
    {
        $response = $this->get('/notifications');

        $response->assertRedirect('/login');
    }
}
