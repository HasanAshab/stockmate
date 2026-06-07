# Database Seeders

This directory contains modular seeders for the StockMate inventory management system.

## Seeder Structure

The seeders are organized in a logical dependency order:

### 1. Foundation Data
- **UserSeeder**: Creates 3 admins + 15 staff members (including demo accounts)
  - Admin: `admin@example.com` / `password`
  - Staff: `staff@example.com` / `password`
- **CategorySeeder**: Creates 10 product categories
- **SupplierSeeder**: Creates 8 suppliers
- **WarehouseSeeder**: Creates 5 warehouses

### 2. Products
- **ProductSeeder**: Creates 50 products linked to categories and suppliers

### 3. Inventory
- **WarehouseStockSeeder**: Creates warehouse stock records
  - Assigns products to 1-3 random warehouses
  - Creates some low-stock items for testing alerts

### 4. Orders
- **PurchaseOrderSeeder**: Creates 30 purchase orders
  - 5 draft orders
  - 10 ordered (awaiting receipt)
  - 15 received (completed)
  - Each with 2-8 line items

- **SalesOrderSeeder**: Creates 45 sales orders
  - 8 pending
  - 25 paid
  - 7 failed
  - 5 cancelled
  - Each with 1-6 line items

### 5. Activity Tracking
- **StockLogSeeder**: Creates ~100-120 stock log entries
  - Stock In and Stock Out transactions
  - Distributed across 30 random products
  - Historical data spanning 3 months

- **NotificationSeeder**: Creates low-stock notifications
  - Sends notifications to all admins for low-stock products
  - Marks some notifications as read

## Usage

### Seed Everything
```bash
php artisan migrate:fresh --seed
```

### Seed Specific Seeder
```bash
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ProductSeeder
```

### Seed in Production (Careful!)
```bash
php artisan db:seed --force
```

## Data Volumes

| Seeder | Count | Notes |
|--------|-------|-------|
| Users | 18 | 3 admins + 15 staff |
| Categories | 10 | |
| Suppliers | 8 | |
| Warehouses | 5 | |
| Products | 50 | |
| Warehouse Stock | ~100 | Products assigned to 1-3 warehouses each |
| Purchase Orders | 30 | With 60-240 line items total |
| Sales Orders | 45 | With 45-270 line items total |
| Stock Logs | ~120 | Historical transactions |
| Notifications | ~6-15 | Low stock alerts |

## Demo Accounts

### Admin Account
- **Email**: `admin@example.com`
- **Password**: `password`
- **Role**: Admin
- **Access**: Full system access

### Staff Account
- **Email**: `staff@example.com`
- **Password**: `password`
- **Role**: Staff
- **Access**: Limited permissions

## Customization

To adjust seeding quantities, edit the respective seeder files:

```php
// database/seeders/ProductSeeder.php
Product::factory()->count(50)->create(); // Change 50 to desired count
```

## Dependencies

Seeders must run in order due to foreign key relationships:

```
Users
  ↓
Categories, Suppliers, Warehouses
  ↓
Products
  ↓
WarehouseStock
  ↓
PurchaseOrders, SalesOrders
  ↓
StockLogs, Notifications
```

## Testing

After seeding, verify the data:

```bash
# Check record counts
php artisan tinker
>>> User::count();
>>> Product::count();
>>> SalesOrder::count();
```

## Notes

- All seeders use factories for consistency
- Faker is used for realistic dummy data
- `WithoutModelEvents` trait prevents event listeners during seeding
- Low-stock items are intentionally created to test notification system
- Historical dates are used for stock logs to simulate real usage

## Maintenance

When adding new models:
1. Create a factory: `php artisan make:factory ModelNameFactory`
2. Create a seeder: `php artisan make:seeder ModelNameSeeder`
3. Add to `DatabaseSeeder::run()` in the correct dependency order
4. Update this README
