<?php

namespace Tests\Feature;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ProcessReadingPlansCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_期日3日前の計画にリマインダーが送られる(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => now()->addDays(3),
        ]);

        $this->artisan('reading-plans:process');

        Notification::assertSentTo($user, ReadingPlanReminder::class, function ($notification) use ($user, $plan) {
            $data = $notification->toDatabase($user);

            return $data['reading_plan_id'] === $plan->id && $data['timing'] === 'three_days_before';
        });
    }

    public function test_期日当日の計画にリマインダーが送られる(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => now(),
        ]);

        $this->artisan('reading-plans:process');

        Notification::assertSentTo($user, ReadingPlanReminder::class, function ($notification) use ($user, $plan) {
            $data = $notification->toDatabase($user);

            return $data['reading_plan_id'] === $plan->id && $data['timing'] === 'on_due_date';
        });
    }

    public function test_期限切れ3日後の計画にリマインダーが送られる(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::Expired,
            'target_date' => now()->subDays(3),
        ]);

        $this->artisan('reading-plans:process');

        Notification::assertSentTo($user, ReadingPlanReminder::class, function ($notification) use ($user, $plan) {
            $data = $notification->toDatabase($user);

            return $data['reading_plan_id'] === $plan->id && $data['timing'] === 'three_days_after';
        });
    }

    public function test_期日を過ぎた進行中の計画は自動的に期限切れになる(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $plan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => now()->subDay(),
        ]);

        $this->artisan('reading-plans:process');

        $this->assertDatabaseHas('reading_plans', [
            'id' => $plan->id,
            'status' => ReadingPlanStatus::Expired->value,
        ]);
    }

    public function test_該当しない日付の計画には通知が送られない(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'status' => ReadingPlanStatus::InProgress,
            'target_date' => now()->addDays(10),
        ]);

        $this->artisan('reading-plans:process');

        Notification::assertNothingSent();
    }
}
