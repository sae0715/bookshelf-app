<?php

namespace App\Http\Requests;

use App\Enums\ReadingPlanStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // 新規作成時のみ書籍選択を受け付ける（編集フォームは期日のみ変更可能なため）
        // 重複制御：1ユーザー・1書籍につき「進行中」の計画は1件までに制限する
        // （理由：一覧の可読性のため。同じ書籍を何度も読み直したい場合は、
        //   1件を「読了」にしてから新しい計画を作る運用を想定）
        if ($this->isMethod('post')) {
            return [
                'book_id' => [
                    'required',
                    'exists:books,id',
                    Rule::unique('reading_plans', 'book_id')->where(function ($query) {
                        return $query->where('user_id', Auth::id())
                            ->where('status', ReadingPlanStatus::InProgress->value);
                    }),
                ],
                'target_date' => ['required', 'date', 'after_or_equal:today'],
            ];
        }

        return [
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => '書籍を選択してください。',
            'book_id.exists' => '選択された書籍が見つかりません。',
            'book_id.unique' => 'この書籍にはすでに進行中の読書計画があります。',
            'target_date.required' => '期日は必須です。',
            'target_date.date' => '期日は有効な日付形式で入力してください。',
            'target_date.after_or_equal' => '期日は本日以降の日付を指定してください。',
        ];
    }
}
