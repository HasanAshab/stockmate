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

---

## GET /api/v1/notifications

```php
it('requires authentication'); // auth:sanctum middleware
it('returns paginated list of all notifications with 200'); // NotificationController::index
it('returns only authenticated user notifications'); // $request->user()->notifications()
it('returns notifications in paginated format'); // cursorPaginate(10)
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns notification resource collection'); // NotificationResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
```

## GET /api/v1/notifications/unread

```php
it('requires authentication'); // auth:sanctum middleware
it('returns paginated list of unread notifications with 200'); // NotificationController::unread
it('returns only unread notifications for authenticated user'); // $request->user()->unreadNotifications()
it('returns notifications in paginated format'); // cursorPaginate(10)
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns notification resource collection'); // NotificationResource::collection
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
```

## GET /api/v1/notifications/unread/count

```php
it('requires authentication'); // auth:sanctum middleware
it('returns unread notification count with 200'); // NotificationController::unreadCount
it('returns count as integer'); // ['data' => ['count' => int]]
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
```

## PATCH /api/v1/notifications/{id}/mark-as-read

```php
it('requires authentication'); // auth:sanctum middleware
it('marks notification as read and returns 204'); // NotificationController::markAsRead
it('only marks authenticated user notifications'); // $request->user()->notifications()->findOrFail($id)
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 404 for non-existent notification'); // findOrFail
it('returns 404 for notification belonging to another user'); // $request->user()->notifications()->findOrFail($id)
$response->assertValidRequest()->assertValidResponse(204);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(404);
```

## PATCH /api/v1/notifications/mark-all-as-read

```php
it('requires authentication'); // auth:sanctum middleware
it('marks all unread notifications as read and returns 204'); // NotificationController::markAllAsRead
it('only marks authenticated user notifications'); // $request->user()->unreadNotifications->markAsRead()
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
$response->assertValidRequest()->assertValidResponse(204);
$response->assertValidRequest()->assertValidResponse(401);
```

## DELETE /api/v1/notifications/{id}

```php
it('requires authentication'); // auth:sanctum middleware
it('deletes notification and returns 204'); // NotificationController::destroy
it('only deletes authenticated user notifications'); // $request->user()->notifications()->findOrFail($id)
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 404 for non-existent notification'); // findOrFail
it('returns 404 for notification belonging to another user'); // $request->user()->notifications()->findOrFail($id)
$response->assertValidRequest()->assertValidResponse(204);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(404);
```
