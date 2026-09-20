<?php

namespace Database\Seeders;

use App\Enums\ReadingPlanStatus;
use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $yamada = User::where('email', 'yamada@example.com')->first();
        $suzuki = User::where('email', 'suzuki@example.com')->first();

        $book = fn (string $title) => Book::where('title', $title)->first();

        $plans = [
            // 進行中：3日前リマインダーが発火する
            ['user' => $yamada, 'book' => $book('サピエンス全史'), 'status' => ReadingPlanStatus::InProgress, 'target_date' => today()->addDays(3), 'completed_at' => null],
            // 進行中：当日リマインダーが発火する
            ['user' => $yamada, 'book' => $book('Clean Code'), 'status' => ReadingPlanStatus::InProgress, 'target_date' => today(), 'completed_at' => null],
            // 進行中：まだ何も発火しない（先の期日）
            ['user' => $yamada, 'book' => $book('嫌われる勇気'), 'status' => ReadingPlanStatus::InProgress, 'target_date' => today()->addDays(10), 'completed_at' => null],
            // 進行中：期日超過→自動失効の対象になる
            ['user' => $yamada, 'book' => $book('火花'), 'status' => ReadingPlanStatus::InProgress, 'target_date' => today()->subDay(), 'completed_at' => null],
            // 期限切れ：3日後リマインダーが発火する
            ['user' => $yamada, 'book' => $book('FACTFULNESS'), 'status' => ReadingPlanStatus::Expired, 'target_date' => today()->subDays(3), 'completed_at' => null],
            // 期限切れ：もう何も発火しない（対象日を過ぎている）
            ['user' => $yamada, 'book' => $book('コンテナ物語'), 'status' => ReadingPlanStatus::Expired, 'target_date' => today()->subDays(10), 'completed_at' => null],
            // 完了：編集不可・削除/取り消しのみ可能の確認用
            ['user' => $yamada, 'book' => $book('吾輩は猫である'), 'status' => ReadingPlanStatus::Completed, 'target_date' => today()->subDays(5), 'completed_at' => today()->subDays(2)],
            // 他ユーザー：認可判定の確認用（山田太郎からは403になる）
            ['user' => $suzuki, 'book' => $book('人を動かす'), 'status' => ReadingPlanStatus::InProgress, 'target_date' => today()->addDays(3), 'completed_at' => null],
        ];

        foreach ($plans as $plan) {
            ReadingPlan::firstOrCreate(
                ['user_id' => $plan['user']->id, 'book_id' => $plan['book']->id],
                [
                    'status' => $plan['status'],
                    'target_date' => $plan['target_date'],
                    'completed_at' => $plan['completed_at'],
                ]
            );
        }
    }
}
