<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
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
        $notifications = $request->user()
            ->notifications()
            ->cursorPaginate(10);

        return NotificationResource::collection($notifications);
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
        $notifications = $request->user()
            ->unreadNotifications()
            ->cursorPaginate(10);
        
        return NotificationResource::collection($notifications);
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
     * @response 204 scenario="Success"
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
     *
     * @response 204 scenario="Success"
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
