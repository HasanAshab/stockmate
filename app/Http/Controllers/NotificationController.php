<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * List All Notifications
     *
     * Get a paginated list of all notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->cursorPaginate(10);

        return NotificationResource::collection($notifications);
    }

    /**
     * List Unread Notifications
     *
     * Get a paginated list of unread notifications for the authenticated user.
     */
    public function unread(Request $request)
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->cursorPaginate(10);

        return NotificationResource::collection($notifications);
    }

    /**
     * Get Unread Count
     *
     * Get the total count of unread notifications for the authenticated user.
     */
    public function unreadCount(Request $request)
    {
        $unreadCount = $request->user()
            ->unreadNotifications()
            ->count();

        return [
            'data' => [
                'count' => $unreadCount,
            ],
        ];
    }

    /**
     * Mark Notification as Read
     *
     * Mark a specific notification as read.
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->noContent();
    }

    /**
     * Mark All Notifications as Read
     *
     * Mark all unread notifications as read for the authenticated user.
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->noContent();
    }

    /**
     * Delete Notification
     *
     * Delete a specific notification.
     */
    public function destroy(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->delete();

        return response()->noContent();
    }
}
