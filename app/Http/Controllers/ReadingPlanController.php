<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\ReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReadingPlanController extends Controller
{
    /**
     * 読書計画の一覧を表示する。
     */
    public function index(Request $request): View
    {
        $currentStatus = $request->input('status');

        $query = ReadingPlan::with('book')->where('user_id', Auth::id());

        if ($currentStatus && in_array($currentStatus, array_column(ReadingPlanStatus::cases(), 'value'), true)) {
            $query->where('status', $currentStatus);
        }

        $readingPlans = $query->orderBy('target_date')->get();

        return view('reading-plans.index', compact('readingPlans', 'currentStatus'));
    }

    /**
     * 新規作成フォームを表示する。
     */
    public function create(): View
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * 新規作成した読書計画を保存する。
     */
    public function store(ReadingPlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        ReadingPlan::create([
            'user_id' => Auth::id(),
            'book_id' => $validated['book_id'],
            'target_date' => $validated['target_date'],
            'status' => ReadingPlanStatus::InProgress,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を登録しました。');
    }

    /**
     * 指定した読書計画の編集フォームを表示する。
     */
    public function edit(ReadingPlan $readingPlan): View
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * 指定した読書計画を更新する。
     */
    public function update(ReadingPlanRequest $request, ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $validated = $request->validated();

        $readingPlan->target_date = $validated['target_date'];

        // 期限切れの計画に未来の期日を再設定した場合は、進行中に戻す
        if ($readingPlan->status === ReadingPlanStatus::Expired) {
            $readingPlan->status = ReadingPlanStatus::InProgress;
        }

        $readingPlan->save();

        return redirect()->route('reading-plans.index')->with('success', '読書計画を更新しました。');
    }

    /**
     * 指定した読書計画を削除する。
     */
    public function destroy(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました。');
    }

    /**
     * 指定した読書計画を読了（完了）にする。
     */
    public function complete(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を読了にしました。');
    }

    /**
     * 指定した読書計画の読了を取り消し、進行中に戻す。
     */
    public function uncomplete(ReadingPlan $readingPlan): RedirectResponse
    {
        $this->authorize('uncomplete', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読了を取り消しました。');
    }
}
