<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * @group Notifications
 *
 * APIs for managing user notifications
 *
 * @authenticated
 */
class NotificationController extends Controller
{
    /**
     * List All Notifications
     *
     * Get a paginated list of all notifications for the authenticated user.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": "uuid-123",
     *       "type": "product-low-stock",
     *       "data": {
     *         "message": "Low stock alert",
     *         "product_name": "Laptop"
     *       },
     *       "read_at": null,
     *       "created_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {},
     *   "links": {}
     * }
     */
    public function index(Request $request)
    {
        return $request->user()
            ->notifications()
            ->cursorPaginate(10);
    }

    /**
     * List Unread Notifications
     *
     * Get a paginated list of unread notifications for the authenticated user.
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": "uuid-123",
     *       "type": "product-low-stock",
     *       "data": {
     *         "message": "Low stock alert"
     *       },
     *       "read_at": null,
     *       "created_at": "2026-01-15T10:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {},
     *   "links": {}
     * }
     */
    public function unread(Request $request)
    {
        return $request->user()
            ->unreadNotifications()
            ->cursorPaginate(10);
    }

    /**
     * Get Unread Count
     *
     * Get the total count of unread notifications for the authenticated user.
     *
     * @response 200 {
     *   "data": {
     *     "count": 5
     *   }
     * }
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
     * @urlParam id string required The notification ID. Example: uuid-123
     *
     * @response 200 {
     *   "id": "uuid-123",
     *   "read_at": "2026-01-15T11:00:00.000000Z"
     * }
     */
    public function markAsRead(Request $request, string $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);

        $notification->markAsRead();

        return response()->json($notification);
    }

    /**
     * Mark All Notifications as Read
     *
     * Mark all unread notifications as read for the authenticated user.
     *
     * @response 200 {
     *   "message": "All notifications marked as read."
     * }
     */
    public function markAllAsRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json([
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * Delete Notification
     *
     * Delete a specific notification.
     *
     * @urlParam id string required The notification ID. Example: uuid-123
     *
     * @response 204 scenario="Success"
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
