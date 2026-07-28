# Dashboard API Tests

## GET /api/v1/dashboard

```php
it('requires authentication'); // auth:sanctum middleware
it('requires dashboard-view permission'); // DashboardPolicy::viewAny via Gate::authorize
it('returns dashboard metrics with 200'); // DashboardController::__invoke
it('returns metrics including total_products'); // GetDashboardMetrics::execute
it('returns metrics including total_low_stock'); // GetDashboardMetrics::execute
it('returns metrics including total_categories'); // GetDashboardMetrics::execute
it('returns metrics including total_suppliers'); // GetDashboardMetrics::execute
it('returns metrics including recent_stock_logs'); // GetDashboardMetrics::execute
it('returns 401 for unauthenticated request'); // auth:sanctum middleware
it('returns 403 for user without dashboard-view permission'); // DashboardPolicy::viewAny
$response->assertValidRequest()->assertValidResponse(200);
$response->assertValidRequest()->assertValidResponse(401);
$response->assertValidRequest()->assertValidResponse(403);
```
