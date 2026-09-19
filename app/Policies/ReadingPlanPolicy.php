<?php

namespace App\Policies;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;

class ReadingPlanPolicy
{
    /**
     * 編集・読了操作が可能か判定する。
     * 所有者本人であること、かつ「完了」ステータスでないことを条件とする。
     * （bladeのUI上の制御をサーバー側でも担保するため）
     */
    public function update(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id
            && $readingPlan->status !== ReadingPlanStatus::Completed;
    }

    /**
     * 削除は所有者本人であればステータスを問わず可能。
     */
    public function delete(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id;
    }

    /**
     * 「読了する」の取り消し（誤操作の救済）。所有者本人 かつ 完了済みの場合のみ許可。
     */
    public function uncomplete(User $user, ReadingPlan $readingPlan): bool
    {
        return $user->id === $readingPlan->user_id
            && $readingPlan->status === ReadingPlanStatus::Completed;
    }
}
