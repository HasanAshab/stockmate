# Activity Log API Tests

## GET /api/v1/activity-logs

```php
it('requires authentication'); // auth:sanctum middleware
it('returns paginated list of activity logs with 200'); // ActivityLogController::index
it('includes causer and subject relationships by default'); // with(['causer', 'subject'])
it('filters activity logs by causer ID'); // AllowedFilter::belongsTo('causer')
it('filters activity logs by subject ID'); // AllowedFilter::belongsTo('subject')
it('filters activity logs by subject_type'); // AllowedFilter::custom('subject_type', FiltersMorphType)
it('filters activity logs by created_at date range'); // AllowedFilter::custom('created_at', FiltersDateRange)
it('sorts activity logs by created_at descending'); // allowedSorts('created_at')
it('includes causer relationship'); // allowedIncludes('causer')
it('includes subject relationship'); // allowedIncludes('subject')
it('returns activity log resource collection'); // ActivityLogResource::collection
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
```
