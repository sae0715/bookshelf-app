<?php

namespace App\Console\Commands;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Notifications\ReadingPlanReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ProcessReadingPlans extends Command
{
    /**
     * @var string
     */
    protected $signature = 'reading-plans:process';

    /**
     * @var string
     */
    protected $description = '読書計画のリマインダー通知を送信し、期限切れの計画を自動的に失効させる（毎日20:00実行）';

    public function handle(): int
    {
        $this->sendReminders();
        $this->expireOverduePlans();

        return self::SUCCESS;
    }

    /**
     * リマインダー：期日3日前・当日・（期限切れになった計画への）3日後の3タイミング。
     */
    private function sendReminders(): void
    {
        $threeDaysBefore = ReadingPlan::with(['user', 'book'])
            ->where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', today()->addDays(3))
            ->get();
        $this->notify($threeDaysBefore, 'three_days_before');

        $onDueDate = ReadingPlan::with(['user', 'book'])
            ->where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', today())
            ->get();
        $this->notify($onDueDate, 'on_due_date');

        $threeDaysAfterExpiry = ReadingPlan::with(['user', 'book'])
            ->where('status', ReadingPlanStatus::Expired)
            ->whereDate('target_date', today()->subDays(3))
            ->get();
        $this->notify($threeDaysAfterExpiry, 'three_days_after');
    }

    /**
     * @param  Collection<int, ReadingPlan>  $plans
     */
    private function notify(Collection $plans, string $timing): void
    {
        foreach ($plans as $plan) {
            $plan->user->notify(new ReadingPlanReminder($plan, $timing));
        }

        $this->info(sprintf('[%s] %d件通知しました。', $timing, $plans->count()));
    }

    /**
     * 自動失効：期日を過ぎた「進行中」の計画をすべて「期限切れ」にする。
     */
    private function expireOverduePlans(): void
    {
        $count = ReadingPlan::where('status', ReadingPlanStatus::InProgress)
            ->whereDate('target_date', '<', today())
            ->update(['status' => ReadingPlanStatus::Expired->value]);

        $this->info("{$count}件の計画を期限切れにしました。");
    }
}
