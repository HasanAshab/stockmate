<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Notifications', 'APIs for managing user notifications')]
#[Authenticated]
class NotificationController extends Controller
{
    /**
     * List All Notifications
     *
     * Get a paginated list of all notifications for the authenticated user.
     */
    #[ResponseFromApiResource(NotificationResource::class, Notification::class, collection: true, paginate: 10)]
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
    #[ResponseFromApiResource(NotificationResource::class, Notification::class, collection: true, paginate: 10)]
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
    #[Response(['data' => ['count' => 5]], 200)]
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
     */
    #[UrlParam('id', 'string', 'The notification ID.', required: true, example: 'uuid-123')]
    #[Response([], 204, 'Success')]
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
    #[Response([], 204, 'Success')]
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
    #[UrlParam('id', 'string', 'The notification ID.', required: true, example: 'uuid-123')]
    #[Response([], 204, 'Success')]
    public function destroy(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->delete();

        return response()->noContent();
    }
}
