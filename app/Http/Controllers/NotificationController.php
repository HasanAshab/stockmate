<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()
            ->notifications()
            ->cursorPaginate(10);
    }

    public function unread(Request $request)
    {
        return $request->user()
            ->unreadNotifications()
            ->cursorPaginate(10);
    }

    public function unreadCount(Request $request)
    {
        $unreadCount = $request->user()
            ->unreadNotifications()
            ->count();

        return [
            "data" => [
                "count" => $unreadCount
            ]
        ];
    }

    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json($notification);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->delete();

        return response()->noContent();
    }
}
