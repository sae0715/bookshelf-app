<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the authenticated user's notifications.
     */
    public function index()
    {
        $notifications = Auth::user()->notifications;

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Mark the specified notification as read.
     */
    public function read(string $id)
    {
        // notifications()リレーションは notifiable_id で自動的に本人のものに絞られるため、
        // 他人の通知IDを渡されても404になる（＝これが認可の役割を兼ねる）
        $notification = Auth::user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        return back()->with('success', '既読にしました。');
    }
}
