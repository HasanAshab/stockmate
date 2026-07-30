# Notification API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetNotificationsTest.php`, `MarkNotificationReadTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Authentication Required**: All notification endpoints require authentication
- **User Isolation**: Users only see their own notifications

## Common Testing Patterns

### Creating Notifications for Testing
```php
$user = User::factory()->create();
$user->notify(new ProductLowStock($product));
$user->notify(new ProductOutOfStock($product));

expect($user->unreadNotifications)->toHaveCount(2);
```

### Testing Read/Unread Status
```php
$notification = $user->notifications()->first();
expect($notification->read_at)->toBeNull();

$notification->markAsRead();
expect($notification->fresh()->read_at)->not->toBeNull();
```

---

## GET /api/v1/notifications
**File**: `tests/Feature/Notification/GetNotificationsTest.php` (suggested)

```php
describe('List Notifications', function () {
    it('requires authentication'); // auth:sanctum + Authenticated attribute
    it('returns paginated list of notifications with 200'); // NotificationController::index
    it('returns only authenticated user notifications'); // $request->user()->notifications()
    it('filters unread notifications'); // unreadNotifications()
    it('includes read notifications by default'); // notifications()
    it('sorts notifications by created_at desc'); // orderBy('created_at', 'desc')
    it('returns notification resource collection'); // NotificationResource::collection
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('does not return other users notifications'); // user isolation
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## POST /api/v1/notifications/{notification}/read
**File**: `tests/Feature/Notification/MarkNotificationReadTest.php` (suggested)

```php
describe('Mark Notification as Read', function () {
    it('requires authentication'); // auth:sanctum + Authenticated attribute
    it('marks notification as read and returns 200'); // NotificationController::markAsRead
    it('sets read_at timestamp'); // $notification->markAsRead()
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 when marking another user notification'); // policy check
    it('returns 404 for non-existent notification'); // route model binding
    it('returns notification resource'); // NotificationResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(403);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## POST /api/v1/notifications/read-all
**File**: `tests/Feature/Notification/MarkAllNotificationsReadTest.php` (suggested)

```php
describe('Mark All Notifications as Read', function () {
    it('requires authentication'); // auth:sanctum + Authenticated attribute
    it('marks all user notifications as read and returns 200'); // NotificationController::markAllAsRead
    it('sets read_at on all unread notifications'); // $user->unreadNotifications()->update()
    it('does not affect other users notifications'); // user isolation
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns success message'); // ['message' => 'All notifications marked as read']
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## DELETE /api/v1/notifications/{notification}
**File**: `tests/Feature/Notification/DeleteNotificationTest.php` (suggested)

```php
describe('Delete Notification', function () {
    it('requires authentication'); // auth:sanctum + Authenticated attribute
    it('deletes notification and returns 204'); // NotificationController::destroy
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 when deleting another user notification'); // policy check
    it('returns 404 for non-existent notification'); // route model binding
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(204);
    $response->assertValidRequest()->assertValidResponse(403);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## Key Testing Notes

- **User Isolation**: Users must only access their own notifications
- **Notification Types**: Test different notification types (ProductLowStock, ProductOutOfStock, etc.)
- **Real-time Updates**: Notifications may be sent via WebSockets/Pusher
- **Database Notifications**: Using Laravel's database notification channel
- **Cleanup**: Old notifications may be automatically deleted after X days
- **Pagination**: Large notification lists should be paginated
- **Unread Count**: May need endpoint to get unread notification count
