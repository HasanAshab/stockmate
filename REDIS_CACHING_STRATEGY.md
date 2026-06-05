# Redis Caching Strategy for StockMate API

## Current State
- **Cache Driver**: Database (SQLite)
- **Redis**: Configured but not enabled
- **Status**: ❌ **NO caching implemented** in any controller or service
- **Impact**: Every request hits the database, even for static/reference data

## Redis Configuration Ready ✅
```env
CACHE_STORE=redis  # Change from 'database' to 'redis'
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_CACHE_DB=1  # Separate database for cache
```

---

## 🔴 CRITICAL - Immediate Performance Wins

### 1. Dashboard Metrics (DashboardController)
**Current Issue**: 4+ queries on EVERY dashboard load
```php
Product::count()              // Query 1
Product::lowStock()->count()  // Query 2
Category::count()             // Query 3
Supplier::count()             // Query 4
StockLog::latest()->with(['user', 'product'])->limit(5)->get()  // Query 5
```

**Solution**: Cache entire dashboard response
```php
public function __invoke()
{
    return Cache::remember('dashboard:metrics', now()->addMinutes(5), function () {
        return [
            'data' => [
                'total_products' => Product::count(),
                'total_low_stock' => Product::lowStock()->count(),
                'total_categories' => Category::count(),
                'total_suppliers' => Supplier::count(),
                'recent_stock_logs' => StockLog::latest()
                    ->limit(5)
                    ->with(['user', 'product'])
                    ->get(),
            ]
        ];
    });
}
```

**Cache Invalidation**: Clear on:
- Product create/update/delete
- Stock changes
- Category create/update/delete
- Supplier create/update/delete

**TTL**: 5 minutes
**Expected Impact**: 4-5 queries → 0 queries (80-90% reduction)

---

### 2. Reference Data - Categories (CategoryController::index)
**Current Issue**: `Category::all()` on every request (used in dropdowns, filters)

**Solution**:
```php
public function index()
{
    Gate::authorize('viewAny', Category::class);

    $categories = Cache::remember('categories:all', now()->addHour(), function () {
        return Category::all();
    });

    return $categories->toResourceCollection();
}
```

**Cache Invalidation**: Clear on:
- Category store/update/destroy/restore

**Implementation**:
```php
// In Category model observer or CategoryController
protected function clearCategoryCache(): void
{
    Cache::forget('categories:all');
    Cache::forget('categories:trashed');
    Cache::forget('dashboard:metrics');
}
```

**TTL**: 1 hour
**Expected Impact**: 100% cache hit rate for reference data

---

### 3. Reference Data - Suppliers (SupplierController::index)
**Current Issue**: `Supplier::all()` on every request

**Solution**:
```php
public function index()
{
    Gate::authorize('viewAny', Supplier::class);

    $suppliers = Cache::remember('suppliers:all', now()->addHour(), function () {
        return Supplier::all();
    });

    return $suppliers->toResourceCollection();
}
```

**Cache Invalidation**: Clear on:
- Supplier store/update/destroy/restore

**TTL**: 1 hour
**Expected Impact**: 100% cache hit rate

---

### 4. Trashed Resources Cache
**Current Issue**: Trashed categories and suppliers query on every admin access

**Solution**:
```php
// CategoryController::trashed()
public function trashed()
{
    Gate::authorize('viewAny', Category::class);

    $trashed = Cache::remember('categories:trashed', now()->addMinutes(30), function () {
        return Category::onlyTrashed()->get();
    });

    return $trashed->toResourceCollection();
}

// SupplierController::trashed()
public function trashed()
{
    Gate::authorize('viewAny', Supplier::class);

    $trashed = Cache::remember('suppliers:trashed', now()->addMinutes(30), function () {
        return Supplier::onlyTrashed()->get();
    });

    return $trashed->toResourceCollection();
}
```

**TTL**: 30 minutes

---

## 🟡 HIGH PRIORITY - Significant Performance Gains

### 5. Product Listings with Filters (ProductController::index)
**Current Issue**: Complex QueryBuilder with multiple relationships on paginated results

**Solution**: Cache by filter combination (complex) OR use flexible cache
```php
public function index()
{
    Gate::authorize('viewAny', Product::class);

    // Cache key based on request parameters
    $cacheKey = 'products:index:' . md5(serialize(request()->all()));

    return Cache::flexible(
        key: $cacheKey,
        ttl: [now()->addMinutes(5), now()->addMinutes(10)],
        callback: function () {
            return QueryBuilder::for(Product::class)
                ->allowedFilters(
                    AllowedFilter::trashed(),
                    AllowedFilter::belongsTo('category'),
                    AllowedFilter::belongsTo('supplier'),
                    AllowedFilter::scope('low_stock'),
                    AllowedFilter::callback('search', function ($query, $value) {
                        $query->where('name', 'ilike', "%{$value}%")
                            ->orWhere('sku', 'ilike', "%{$value}%");
                    }),
                )
                ->allowedSorts('price', 'quantity', 'created_at')
                ->allowedIncludes('category', 'supplier', 'media')
                ->defaultSort('-created_at')
                ->paginate(10)
                ->appends(request()->query());
        }
    );
}
```

**Alternative - Simple Time-Based Cache**:
```php
// Cache only the default unfiltered list
if (!request()->hasAny(['filter', 'sort', 'include'])) {
    return Cache::remember('products:default-list', now()->addMinutes(2), function () {
        return Product::query()
            ->defaultSort('-created_at')
            ->paginate(10);
    });
}
```

**Cache Invalidation**: Clear on Product changes
**TTL**: 2-5 minutes
**Expected Impact**: 30-50% query reduction on common filter combinations

---

### 6. Warehouse Stock Details (WarehouseController::stock)
**Current Issue**: Nested relationship `product.category` on 20 paginated items

**Solution**:
```php
public function stock(Warehouse $warehouse)
{
    Gate::authorize('viewStock', $warehouse);

    $cacheKey = "warehouse:{$warehouse->id}:stock:page:" . request('page', 1);

    return Cache::remember($cacheKey, now()->addMinutes(3), function () use ($warehouse) {
        return WarehouseStock::query()
            ->whereBelongsTo($warehouse)
            ->with(['product.category'])
            ->paginate(20);
    });
}
```

**Cache Invalidation**: Clear on:
- Stock level changes for the warehouse
- Stock transfers

**TTL**: 3 minutes

---

### 7. Activity Logs (ActivityLogController::index)
**Current Issue**: Polymorphic relationships on audit trail queries

**Solution**: Cache paginated results
```php
public function index()
{
    Gate::authorize('viewAny', auth()->user());

    $filters = request()->all();
    $cacheKey = 'activity-logs:' . md5(serialize($filters));

    return Cache::remember($cacheKey, now()->addMinutes(10), function () {
        return QueryBuilder::for(ActivityLog::class)
            ->allowedFilters(
                AllowedFilter::exact('causer_id'),
                AllowedFilter::exact('subject_type'),
                AllowedFilter::custom('created_at', new FiltersDateRange),
            )
            ->allowedIncludes('causer', 'subject')
            ->with(['causer', 'subject'])
            ->latest()
            ->paginate(15)
            ->appends(request()->query());
    });
}
```

**TTL**: 10 minutes (audit logs rarely change)

---

## 🟢 MODERATE PRIORITY - Optimization Opportunities

### 8. Stock Log History (StockLogController::index)
**Solution**: Cache filtered results
```php
$cacheKey = 'stock-logs:' . md5(serialize(request()->all()));
return Cache::remember($cacheKey, now()->addMinutes(5), function () {
    // existing QueryBuilder logic
});
```

**TTL**: 5 minutes

---

### 9. Purchase Orders List (PurchaseOrderController::index)
**Solution**: Cache with flexible TTL
```php
$cacheKey = 'purchase-orders:' . md5(serialize(request()->all()));
return Cache::flexible(
    $cacheKey,
    [now()->addMinutes(3), now()->addMinutes(5)],
    function () {
        // existing QueryBuilder logic
    }
);
```

**TTL**: 3-5 minutes

---

### 10. Sales Orders List (SalesOrderController::index)
**Solution**: Same as purchase orders
**TTL**: 3-5 minutes

---

## 🔵 ADVANCED - Query Result Caching

### 11. Stock Log Export (StockLogController::export)
**Current Issue**: Can load thousands of records with relationships

**Solution**: Cache query results by date range
```php
public function export(ExportStockLogRequest $request, string $format)
{
    $startDate = $request->validated('start_date');
    $endDate = $request->validated('end_date');
    $cacheKey = "stock-logs:export:{$startDate}:{$endDate}";

    $stockLogs = Cache::remember($cacheKey, now()->addHours(24), function () use ($request) {
        $query = StockLog::query()
            ->with(['product', 'user'])
            ->whereBetween('created_at', [
                $request->validated('start_date'),
                $request->validated('end_date'),
            ]);

        return $query->get();
    });

    return (new ExportStockLog($stockLogs))->download("stock-logs.{$format}");
}
```

**TTL**: 24 hours (historical data doesn't change)

---

## 🔧 Implementation Strategy

### Phase 1: Quick Wins (Week 1)
1. ✅ Enable Redis: Change `CACHE_STORE=redis` in `.env`
2. ✅ Dashboard caching (5 min TTL)
3. ✅ Category list caching (1 hour TTL)
4. ✅ Supplier list caching (1 hour TTL)
5. ✅ Add cache invalidation in CategoryController and SupplierController

**Expected Result**: 60-70% query reduction on dashboard and reference data

### Phase 2: List Endpoints (Week 2)
1. ✅ Product listings cache (5 min TTL)
2. ✅ Warehouse stock cache (3 min TTL)
3. ✅ Activity logs cache (10 min TTL)
4. ✅ Trashed resources cache (30 min TTL)

**Expected Result**: 40-50% query reduction on list endpoints

### Phase 3: Transaction History (Week 3)
1. ✅ Purchase orders cache (3-5 min flexible)
2. ✅ Sales orders cache (3-5 min flexible)
3. ✅ Stock logs cache (5 min TTL)

**Expected Result**: 30-40% query reduction on order history

### Phase 4: Advanced Caching (Week 4)
1. ✅ Export query caching (24 hour TTL)
2. ✅ Implement cache tags for group invalidation
3. ✅ Monitor cache hit rates with Laravel Telescope
4. ✅ Set up cache warming for critical data

---

## 📊 Cache Invalidation Strategy

### Model Observers Approach
Create observers to auto-invalidate cache on model changes:

```php
// app/Observers/CategoryObserver.php
class CategoryObserver
{
    public function created(Category $category): void
    {
        $this->clearCache();
    }

    public function updated(Category $category): void
    {
        $this->clearCache();
    }

    public function deleted(Category $category): void
    {
        $this->clearCache();
    }

    public function restored(Category $category): void
    {
        $this->clearCache();
    }

    protected function clearCache(): void
    {
        Cache::forget('categories:all');
        Cache::forget('categories:trashed');
        Cache::forget('dashboard:metrics');
        
        // Clear product caches since they include categories
        Cache::flush(); // Or use tags: Cache::tags(['products'])->flush();
    }
}
```

Register in `AppServiceProvider`:
```php
public function boot(): void
{
    Category::observe(CategoryObserver::class);
    Supplier::observe(SupplierObserver::class);
    Product::observe(ProductObserver::class);
}
```

---

## 🏷️ Cache Tags Strategy (Recommended)

Use Redis cache tags for better invalidation control:

```php
// Storing with tags
Cache::tags(['dashboard', 'products'])->remember('dashboard:metrics', 300, function () {
    // ...
});

// Invalidating by tag
Cache::tags(['products'])->flush();  // Clears all product-related caches
Cache::tags(['dashboard'])->flush(); // Clears all dashboard caches
```

**Tag Groups**:
- `dashboard` - Dashboard metrics
- `products` - Product listings, detail
- `categories` - Category lists
- `suppliers` - Supplier lists
- `orders` - Purchase/Sales orders
- `stock` - Stock logs, warehouse stock
- `activity` - Activity logs

---

## 🔍 Monitoring & Debugging

### 1. Install Laravel Debugbar (Development)
```bash
composer require barryvdh/laravel-debugbar --dev
```

### 2. Monitor Cache Hit Rates
```php
// In AppServiceProvider
Cache::hitting(function ($key, $value) {
    logger()->info("Cache HIT: {$key}");
});

Cache::missing(function ($key) {
    logger()->info("Cache MISS: {$key}");
});
```

### 3. Cache Performance Metrics
```php
// Create a middleware to log cache stats
public function handle($request, Closure $next)
{
    $startQueries = DB::getQueryLog()->count();
    
    $response = $next($request);
    
    $endQueries = DB::getQueryLog()->count();
    $queryCount = $endQueries - $startQueries;
    
    logger()->info("Queries executed: {$queryCount}");
    
    return $response;
}
```

---

## 📈 Expected Performance Improvements

| Endpoint | Current Queries | With Cache | Improvement |
|----------|----------------|------------|-------------|
| Dashboard | 4-5 | 0 | **100%** |
| Categories List | 1 | 0 | **100%** |
| Suppliers List | 1 | 0 | **100%** |
| Products List | 10-15 | 0-2 | **80-90%** |
| Warehouse Stock | 20-40 | 0 | **100%** |
| Activity Logs | 15-30 | 0 | **100%** |
| Purchase Orders | 30-60 | 0-5 | **85-95%** |
| Sales Orders | 30-60 | 0-5 | **85-95%** |
| Stock Logs | 10-20 | 0-2 | **80-90%** |

**Overall Expected Reduction**: 70-85% fewer database queries across the application

---

## ⚠️ Important Considerations

### 1. Cache Stampede Prevention
Use `Cache::lock()` for high-traffic endpoints:
```php
$lock = Cache::lock('dashboard:metrics:lock', 10);

if ($lock->get()) {
    try {
        $data = Cache::remember('dashboard:metrics', 300, function () {
            // expensive query
        });
    } finally {
        $lock->release();
    }
}
```

### 2. Flexible Cache for Stale-While-Revalidate
```php
Cache::flexible(
    key: 'dashboard:metrics',
    ttl: [now()->addMinutes(5), now()->addMinutes(10)],  // [fresh, stale]
    callback: function () {
        // expensive query
    }
);
```

### 3. Per-User Cache Keys
For user-specific data:
```php
$cacheKey = "user:{$userId}:notifications";
```

### 4. Cache Warming
Pre-populate critical caches on deployment:
```bash
php artisan cache:warm
```

Create a custom command:
```php
// app/Console/Commands/WarmCache.php
public function handle()
{
    Cache::remember('dashboard:metrics', 300, fn() => /* ... */);
    Cache::remember('categories:all', 3600, fn() => Category::all());
    Cache::remember('suppliers:all', 3600, fn() => Supplier::all());
}
```

---

## 🚀 Quick Start Checklist

- [ ] Update `.env`: `CACHE_STORE=redis`
- [ ] Test Redis connection: `php artisan cache:clear`
- [ ] Implement dashboard caching
- [ ] Implement category caching
- [ ] Implement supplier caching
- [ ] Create CategoryObserver for cache invalidation
- [ ] Create SupplierObserver for cache invalidation
- [ ] Test cache invalidation on CRUD operations
- [ ] Monitor cache hit rates
- [ ] Proceed to Phase 2

---

## 📝 Notes

- Redis must be installed and running (`redis-server`)
- Test cache invalidation thoroughly in staging
- Monitor memory usage (Redis RAM)
- Consider cache warming for production deploys
- Use cache tags when available (Redis/Memcached only)
- Document cache keys in a central location
