# Scout Search Migration Summary

## Overview
Successfully migrated from Spatie Query Builder's filter search to Laravel Scout for full-text search functionality across all models.

## Changes Made

### 1. Models Updated with Scout Searchable Trait

All the following models now have the `Searchable` trait and `toSearchableArray()` method:

#### Product Model
- **Searchable fields**: `name`, `sku`
- **Search strategy**: `SearchUsingFullText(['name', 'sku'])` - Uses full-text index for fast searching
- **Previous search**: Spatie filter with `AllowedFilter::groupOr` on name and sku
- **Location**: `app/Models/Product.php`

#### User Model
- **Searchable fields**: `name`, `email`
- **Search strategy**: `SearchUsingFullText(['name', 'email'])` - Uses full-text index
- **Previous search**: Spatie filter with `AllowedFilter::groupOr` on name and email
- **Location**: `app/Models/User.php`

#### SalesOrder Model
- **Searchable fields**: `customer_name`, `customer_email`, `customer_phone`, `transaction_reference`
- **Search strategy**: `SearchUsingFullText(['customer_name', 'customer_email', 'customer_phone', 'transaction_reference'])` - Uses full-text index
- **Previous search**: Spatie filter with `AllowedFilter::groupOr` on all customer fields
- **Location**: `app/Models/SalesOrder.php`

#### PurchaseOrder Model
- **Searchable fields**: `note`
- **Search strategy**: `SearchUsingFullText('note')` - Uses full-text index for note field
- **Previous search**: Spatie filter with `AllowedFilter::partial` on note
- **Location**: `app/Models/PurchaseOrder.php`

#### StockLog Model
- **Searchable fields**: `note`
- **Search strategy**: `SearchUsingFullText('note')` - Uses full-text index for note field
- **Previous search**: Spatie filter with `AllowedFilter::partial` on note
- **Location**: `app/Models/StockLog.php`

#### Warehouse Model
- **Searchable fields**: `name`, `location`
- **Search strategy**: `SearchUsingFullText(['name', 'location'])` - Uses full-text index
- **Previous search**: None (newly added to search)
- **Location**: `app/Models/Warehouse.php`

### 2. Controllers Updated

Removed Spatie search filters from the following controllers:
- `ProductController::index()` - Removed `AllowedFilter::groupOr('search')`
- `UserController::index()` - Removed `AllowedFilter::groupOr('search')`
- `SalesOrderController::index()` - Removed `AllowedFilter::groupOr('search')`
- `PurchaseOrderController::index()` - Removed `AllowedFilter::partial('search')`
- `StockLogController::index()` - Removed `AllowedFilter::partial('search')`

### 3. Search Resources Created

Created dedicated search result resources in `app/Http/Resources/Search/`:
- `ProductSearchResultResource.php`
- `UserSearchResultResource.php`
- `SalesOrderSearchResultResource.php`
- `PurchaseOrderSearchResultResource.php`
- `StockLogSearchResultResource.php`
- `WarehouseSearchResultResource.php`

Each resource includes:
- `id` - Model ID
- `type` - Model type identifier (e.g., 'product', 'user')
- Relevant searchable fields
- Additional context fields (status, dates, etc.)

### 4. Search Configuration Updated

**File**: `config/search.php`

Added all six models to the search scopes:
```php
'scopes' => [
    'sales_orders' => [...],
    'products' => [...],
    'warehouses' => [...],
    'purchase_orders' => [...],    // NEW
    'stock_logs' => [...],         // NEW
    'users' => [...],              // NEW
]
```

### 5. Database Migration

**File**: `database/migrations/2026_07_21_054804_add_fulltext_indexes_for_scout.php`

Created migration to add full-text indexes for MySQL/PostgreSQL:
- Products: `fulltext(['name', 'sku'])`
- Users: `fulltext(['name', 'email'])`
- Sales Orders: `fulltext(['customer_name', 'customer_email', 'customer_phone', 'transaction_reference'])`
- Warehouses: `fulltext(['name', 'location'])`
- Purchase Orders: `fulltext('note')`
- Stock Logs: `fulltext('note')`

**Note**: SQLite detection added - indexes are only created for MySQL/PostgreSQL as SQLite requires different FTS5 setup.

### 6. Environment Configuration

Updated both `.env` and `.env.example` with Scout configuration:
```env
SCOUT_DRIVER=database
SCOUT_QUEUE=false
```

### 7. Morph Map Updated

**File**: `app/Providers/AppServiceProvider.php`

Added all loggable models to the morph map for activity logging:
- sales-order-item
- purchase-order
- purchase-order-item
- stock-log
- supplier
- warehouse
- warehouse-stock

## Scout Import Status

All models have been imported into Scout:
- ✅ Product
- ✅ User
- ✅ SalesOrder
- ✅ PurchaseOrder
- ✅ StockLog
- ✅ Warehouse

## API Endpoints

The search functionality is available through `SearchController`:

### Global Search (All Resources)
```
GET /api/v1/search?q=search_term
```
Returns results from all models the user is authorized to view.

### Scoped Search (Single Resource)
```
GET /api/v1/search/{scope}?q=search_term
```
Available scopes:
- `products`
- `users`
- `sales_orders`
- `purchase_orders`
- `stock_logs`
- `warehouses`

### Configuration
- **Global search**: Min length 3 chars, max 5 results per scope
- **Scoped search**: Min length 2 chars, max 10 results

## Implementation Details

### Search Architecture
1. **PerformSearch Action** (`app/Actions/Search/PerformSearch.php`)
   - Executes Scout search queries
   - Handles minimum length validation
   - Applies result limits

2. **SearchController** (`app/Http/Controllers/SearchController.php`)
   - `searchAll()` - Global search across all authorized models
   - `searchScope()` - Single model search with authorization check
   - Uses config-driven approach for model lookup (no direct class resolution from user input)

3. **Scout Configuration** (`config/scout.php`)
   - Driver: `database` (uses existing database with full-text/LIKE search)
   - Queue: `false` (synchronous indexing)
   - After commit: `false`
   - Soft deletes: `false`

## Benefits of Scout Migration

1. **Unified Search API**: Consistent search experience across all models
2. **Automatic Index Sync**: Scout observers keep indexes updated automatically
3. **Better Performance**: Full-text indexes (on MySQL/PostgreSQL) provide faster searches with `SearchUsingFullText` attributes
4. **Optimized Search Strategy**: Each model uses `SearchUsingFullText` attribute to leverage database full-text indexes automatically
5. **Relevance Ordering**: Scout database engine orders results by relevance
6. **Scalability**: Easy to switch to third-party engines (Algolia, Meilisearch, Typesense) later
7. **No External Dependencies**: Database engine works without additional infrastructure
8. **Intelligent Search**: On MySQL/PostgreSQL, uses `MATCH...AGAINST` for better relevance; on SQLite, falls back to `LIKE` patterns

## Testing Recommendations

Test the following scenarios:

1. **Global Search**:
   ```bash
   curl "http://localhost:8000/api/v1/search?q=test"
   ```

2. **Product Search**:
   ```bash
   curl "http://localhost:8000/api/v1/search/products?q=laptop"
   ```

3. **User Search**:
   ```bash
   curl "http://localhost:8000/api/v1/search/users?q=john"
   ```

4. **Sales Order Search**:
   ```bash
   curl "http://localhost:8000/api/v1/search/sales_orders?q=customer_email"
   ```

## Future Considerations

1. **Queue Support**: For production with high traffic, enable `SCOUT_QUEUE=true` in `.env`
2. **Search Engine Upgrade**: If search volume grows, consider Meilisearch or Algolia
3. **SQLite FTS5**: For better SQLite search performance, implement FTS5 virtual tables
4. **Search Analytics**: Track popular searches and improve relevance

## Maintenance Commands

```bash
# Re-import all models (after bulk updates)
php artisan scout:import "App\Models\Product"
php artisan scout:import "App\Models\User"
php artisan scout:import "App\Models\SalesOrder"
php artisan scout:import "App\Models\PurchaseOrder"
php artisan scout:import "App\Models\StockLog"
php artisan scout:import "App\Models\Warehouse"

# Clear search index for a model
php artisan scout:flush "App\Models\Product"

# Queue-based import (for large datasets)
php artisan scout:queue-import "App\Models\Product"
```

## Notes

- All search filters removed from Spatie Query Builder - search is now exclusively through SearchController
- Filter functionality (status, dates, relationships) remains in controllers via QueryBuilder
- Scout automatically handles create/update/delete syncing - no manual index updates needed
- Authorization is checked via policies before searching (respects `viewAny` ability)
