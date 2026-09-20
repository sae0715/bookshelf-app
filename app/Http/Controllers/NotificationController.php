<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Display a listing of the authenticated user's notifications.
     */
    public function index(): View
    {
        $notifications = Auth::user()->notifications;

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark the specified notification as read.
     */
    public function read(string $id): RedirectResponse
    {
        // notifications()リレーションは notifiable_id で自動的に本人のものに絞られるため、
        // 他人の通知IDを渡されても404になる（＝これが認可の役割を兼ねる）
        $notification = Auth::user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        return back()->with('success', '既読にしました。');
    }
}
