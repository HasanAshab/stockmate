# Activity Log API Tests

## Test Organization

Tests should be organized by endpoint into separate files:
- One endpoint per file (e.g., `GetActivityLogsTest.php`)
- Use `describe()` blocks with descriptive names

## Testing Guidelines

- **Spectator Setup**: Configured globally in `tests/Pest.php` - do not add to individual test files
- **Contract Testing**: Always use `$response->assertValidRequest()->assertValidResponse(status)`
- **No Duplicate Assertions**: Do not use `assertJsonStructure()` - `assertValidResponse()` already validates against OpenAPI schema
- **File Organization**: One endpoint per file with `describe()` block using descriptive name
- **Read-Only**: Activity logs are system-generated, no create/update/delete endpoints
- **Spatie Activity Log**: Uses spatie/laravel-activitylog package

## Common Testing Patterns

### Testing Activity Logging
```php
// Create activity
$user = User::factory()->create();
$product = Product::factory()->create(['name' => 'Laptop']);

activity()
    ->causedBy($user)
    ->performedOn($product)
    ->log('Product created');

// Verify log exists
$response = actingAs($user)->getJson('/api/v1/activity-logs');
expect($response->json('data'))->toHaveCount(1);
```

### Testing Log Filtering
```php
activity()->causedBy($user)->log('User logged in');
activity()->performedOn($product)->log('Product updated');

// Filter by causer
$response = getJson('/api/v1/activity-logs?filter[causer_id]=' . $user->id);

// Filter by subject
$response = getJson('/api/v1/activity-logs?filter[subject_type]=Product&filter[subject_id]=' . $product->id);
```

---

## GET /api/v1/activity-logs
**File**: `tests/Feature/ActivityLog/GetActivityLogsTest.php` (suggested)

```php
describe('List Activity Logs', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires activity-logs-view permission'); // ActivityLogPolicy::viewAny
    it('returns paginated list of activity logs with 200'); // ActivityLogController::index
    it('includes causer (user) relationship'); // with('causer')
    it('includes subject (model) relationship'); // with('subject')
    it('filters by causer ID'); // AllowedFilter::exact('causer_id')
    it('filters by causer type'); // AllowedFilter::exact('causer_type')
    it('filters by subject ID'); // AllowedFilter::exact('subject_id')
    it('filters by subject type'); // AllowedFilter::exact('subject_type')
    it('filters by description'); // AllowedFilter::partial('description')
    it('filters by log name'); // AllowedFilter::exact('log_name')
    it('filters by created_at date range'); // AllowedFilter::custom
    it('sorts by created_at descending'); // defaultSort('-created_at')
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // ActivityLogPolicy::viewAny
    it('returns activity log resource collection'); // ActivityLogResource::collection
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
});
```

---

## GET /api/v1/activity-logs/{activityLog}
**File**: `tests/Feature/ActivityLog/ShowActivityLogTest.php` (suggested)

```php
describe('Show Activity Log', function () {
    it('requires authentication'); // auth:sanctum middleware
    it('requires activity-logs-view permission'); // ActivityLogPolicy::view
    it('returns activity log details with 200'); // ActivityLogController::show
    it('includes causer relationship'); // with('causer')
    it('includes subject relationship'); // with('subject')
    it('includes properties JSON data'); // properties column
    it('returns 401 for unauthenticated request'); // auth:sanctum middleware
    it('returns 403 for user without permission'); // ActivityLogPolicy::view
    it('returns 404 for non-existent activity log'); // route model binding
    it('returns activity log resource'); // ActivityLogResource
    
    // Contract testing
    $response->assertValidRequest()->assertValidResponse(200);
    $response->assertValidRequest()->assertValidResponse(404);
});
```

---

## Key Testing Notes

- **Automatic Logging**: Most logs are created automatically by models using LogsActivity trait
- **Causer**: The user who performed the action (can be null for system actions)
- **Subject**: The model that was affected by the action
- **Properties**: JSON field containing old/new attributes for updates
- **Log Names**: Group logs by context (e.g., 'auth', 'products', 'orders')
- **Description**: Human-readable description of the action
- **Polymorphic Relations**: Both causer and subject are polymorphic
- **Read-Only**: Activity logs should never be modified or deleted through API
- **Performance**: Index on created_at, causer_id, subject_id for fast filtering
- **Retention**: Consider auto-deletion of old logs (e.g., > 90 days)
