<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminder extends Notification
{
    use Queueable;

    public function __construct(
        private readonly ReadingPlan $plan,
        private readonly string $timing,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(mixed $notifiable): array
    {
        return [
            'reading_plan_id' => $this->plan->id,
            'timing' => $this->timing,
            'title' => $this->title(),
            'body' => $this->body(),
        ];
    }

    private function title(): string
    {
        return match ($this->timing) {
            'three_days_before' => '読書計画のリマインダー',
            'on_due_date' => '読書計画の期日です',
            'three_days_after' => '読書計画が期限切れです',
            default => '読書計画の通知',
        };
    }

    private function body(): string
    {
        $bookTitle = $this->plan->book->title;

        return match ($this->timing) {
            'three_days_before' => "「{$bookTitle}」の期日まであと3日です。",
            'on_due_date' => "「{$bookTitle}」の期日は本日です。",
            'three_days_after' => "「{$bookTitle}」は期限切れになってから3日が経過しています。",
            default => "「{$bookTitle}」の読書計画をご確認ください。",
        };
    }
}
