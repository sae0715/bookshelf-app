<?php

namespace App\Http\Controllers;

use App\Enums\ReadingPlanStatus;
use App\Http\Requests\ReadingPlanRequest;
use App\Models\Book;
use App\Models\ReadingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReadingPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $books = Book::orderBy('title')->get();

        return view('reading-plans.create', compact('books'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ReadingPlanRequest $request)
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
     * Show the form for editing the specified resource.
     */
    public function edit(ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        return view('reading-plans.edit', compact('readingPlan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ReadingPlanRequest $request, ReadingPlan $readingPlan)
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
     * Remove the specified resource from storage.
     */
    public function destroy(ReadingPlan $readingPlan)
    {
        $this->authorize('delete', $readingPlan);

        $readingPlan->delete();

        return redirect()->route('reading-plans.index')->with('success', '読書計画を削除しました。');
    }

    /**
     * Mark the specified reading plan as completed.
     */
    public function complete(ReadingPlan $readingPlan)
    {
        $this->authorize('update', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::Completed,
            'completed_at' => now(),
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読書計画を読了にしました。');
    }

    /**
     * Revert the specified reading plan from completed back to in-progress.
     */
    public function uncomplete(ReadingPlan $readingPlan)
    {
        $this->authorize('uncomplete', $readingPlan);

        $readingPlan->update([
            'status' => ReadingPlanStatus::InProgress,
            'completed_at' => null,
        ]);

        return redirect()->route('reading-plans.index')->with('success', '読了を取り消しました。');
    }
}
