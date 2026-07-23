<?php

namespace App\Enums;

enum Role: string
{
    case SuperAdmin = 'super-admin';
    case Administrator = 'administrator';
    case StoreManager = 'store-manager';
    case WarehouseManager = 'warehouse-manager';
    case WarehouseStaff = 'warehouse-staff';
    case PurchasingOfficer = 'purchasing-officer';
    case SalesRepresentative = 'sales-representative';
    case Cashier = 'cashier';
    case InventoryClerk = 'inventory-clerk';
    case Accountant = 'accountant';
    case Auditor = 'auditor';
    case Viewer = 'viewer';

    public function permissions(): array
    {
        return match ($this) {
            // Super Admin: Full access to everything through gate bypass
            self::SuperAdmin => [],

            // Administrator: Manages users, settings, products, warehouses
            self::Administrator => [
                Permission::DashboardView,

                // Products
                Permission::ProductsView,
                Permission::ProductsCreate,
                Permission::ProductsUpdate,
                Permission::ProductsDelete,

                // Categories
                Permission::CategoriesView,
                Permission::CategoriesCreate,
                Permission::CategoriesUpdate,
                Permission::CategoriesDelete,
                Permission::CategoriesRestore,
                Permission::CategoriesForceDelete,

                // Suppliers
                Permission::SuppliersView,
                Permission::SuppliersCreate,
                Permission::SuppliersUpdate,
                Permission::SuppliersDelete,
                Permission::SuppliersRestore,
                Permission::SuppliersForceDelete,

                // Warehouses
                Permission::WarehousesView,
                Permission::WarehousesCreate,
                Permission::WarehousesUpdate,
                Permission::WarehousesDelete,

                // Purchase Orders
                Permission::PurchaseOrdersView,
                Permission::PurchaseOrdersCreate,
                Permission::PurchaseOrdersUpdate,
                Permission::PurchaseOrdersDelete,
                Permission::PurchaseOrdersCancel,
                Permission::PurchaseOrdersMarkOrdered,
                Permission::PurchaseOrdersReceive,

                // Sales Orders
                Permission::SalesOrdersView,
                Permission::SalesOrdersCreate,
                Permission::SalesOrdersCancel,
                Permission::SalesOrdersInitiatePayment,

                // Stock Logs
                Permission::StockLogsView,
                Permission::StockLogsCreate,
                
                // Roles and Permissions 
                Permission::RolesManage,
                Permission::PermissionsManage,

                // Users
                Permission::UsersDeactivate,
            ],

            // Store Manager: Runs daily operations, reports, inventory
            self::StoreManager => [
                Permission::DashboardView,

                // Products
                Permission::ProductsView,
                Permission::ProductsCreate,
                Permission::ProductsUpdate,

                // Categories
                Permission::CategoriesView,
                Permission::CategoriesCreate,
                Permission::CategoriesUpdate,

                // Suppliers
                Permission::SuppliersView,

                // Warehouses
                Permission::WarehousesView,

                // Purchase Orders
                Permission::PurchaseOrdersView,
                Permission::PurchaseOrdersCreate,
                Permission::PurchaseOrdersReceive,

                // Sales Orders
                Permission::SalesOrdersView,
                Permission::SalesOrdersCreate,
                Permission::SalesOrdersInitiatePayment,

                // Stock Logs
                Permission::StockLogsView,
                Permission::StockLogsCreate,
            ],

            // Warehouse Manager: Oversees warehouses, transfers, stock adjustments
            self::WarehouseManager => [
                Permission::DashboardView,

                // Products
                Permission::ProductsView,
                Permission::ProductsUpdate,

                // Categories
                Permission::CategoriesView,

                // Suppliers
                Permission::SuppliersView,

                // Warehouses
                Permission::WarehousesView,
                Permission::WarehousesUpdate,

                // Purchase Orders
                Permission::PurchaseOrdersView,
                Permission::PurchaseOrdersReceive,

                // Sales Orders
                Permission::SalesOrdersView,

                // Stock Logs
                Permission::StockLogsView,
                Permission::StockLogsCreate,
            ],

            // Warehouse Staff: Receives, picks, transfers, counts stock
            self::WarehouseStaff => [
                // Products
                Permission::ProductsView,

                // Warehouses
                Permission::WarehousesView,

                // Purchase Orders
                Permission::PurchaseOrdersView,
                Permission::PurchaseOrdersReceive,

                // Sales Orders
                Permission::SalesOrdersView,

                // Stock Logs
                Permission::StockLogsView,
                Permission::StockLogsCreate,
            ],

            // Purchasing Officer: Purchase orders, suppliers, receiving goods
            self::PurchasingOfficer => [
                Permission::DashboardView,

                // Products
                Permission::ProductsView,

                // Categories
                Permission::CategoriesView,

                // Suppliers
                Permission::SuppliersView,
                Permission::SuppliersCreate,
                Permission::SuppliersUpdate,

                // Warehouses
                Permission::WarehousesView,

                // Purchase Orders
                Permission::PurchaseOrdersView,
                Permission::PurchaseOrdersCreate,
                Permission::PurchaseOrdersUpdate,
                Permission::PurchaseOrdersCancel,
                Permission::PurchaseOrdersMarkOrdered,
                Permission::PurchaseOrdersReceive,

                // Stock Logs
                Permission::StockLogsView,
            ],

            // Sales Representative: Creates sales orders, views customers
            self::SalesRepresentative => [
                // Products
                Permission::ProductsView,

                // Categories
                Permission::CategoriesView,

                // Warehouses
                Permission::WarehousesView,

                // Sales Orders
                Permission::SalesOrdersView,
                Permission::SalesOrdersCreate,
                Permission::SalesOrdersInitiatePayment,

                // Stock Logs
                Permission::StockLogsView,
            ],

            // Cashier: POS/counter sales, payments, receipts
            self::Cashier => [
                // Products
                Permission::ProductsView,

                // Categories
                Permission::CategoriesView,

                // Warehouses
                Permission::WarehousesView,

                // Sales Orders
                Permission::SalesOrdersView,
                Permission::SalesOrdersCreate,
                Permission::SalesOrdersInitiatePayment,

                // Stock Logs
                Permission::StockLogsView,
            ],

            // Inventory Clerk: Product maintenance, stock counts, inventory updates
            self::InventoryClerk => [
                // Products
                Permission::ProductsView,
                Permission::ProductsCreate,
                Permission::ProductsUpdate,

                // Categories
                Permission::CategoriesView,

                // Suppliers
                Permission::SuppliersView,

                // Warehouses
                Permission::WarehousesView,

                // Stock Logs
                Permission::StockLogsView,
                Permission::StockLogsCreate,
            ],

            // Accountant: Payments, invoices, financial reports
            self::Accountant => [
                Permission::DashboardView,

                // Products
                Permission::ProductsView,

                // Suppliers
                Permission::SuppliersView,

                // Purchase Orders
                Permission::PurchaseOrdersView,

                // Sales Orders
                Permission::SalesOrdersView,
                Permission::SalesOrdersCancel,

                // Stock Logs
                Permission::StockLogsView,
            ],

            // Auditor: Read-only access to reports and logs
            self::Auditor => [
                Permission::DashboardView,

                // Products
                Permission::ProductsView,

                // Categories
                Permission::CategoriesView,

                // Suppliers
                Permission::SuppliersView,

                // Warehouses
                Permission::WarehousesView,

                // Purchase Orders
                Permission::PurchaseOrdersView,

                // Sales Orders
                Permission::SalesOrdersView,

                // Stock Logs
                Permission::StockLogsView,
            ],

            // Viewer: Read-only access to permitted modules
            self::Viewer => [
                Permission::DashboardView,

                // Products
                Permission::ProductsView,

                // Categories
                Permission::CategoriesView,

                // Suppliers
                Permission::SuppliersView,

                // Warehouses
                Permission::WarehousesView,

                // Stock Logs
                Permission::StockLogsView,
            ],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator',
            self::Administrator => 'Administrator',
            self::StoreManager => 'Store Manager',
            self::WarehouseManager => 'Warehouse Manager',
            self::WarehouseStaff => 'Warehouse Staff',
            self::PurchasingOfficer => 'Purchasing Officer',
            self::SalesRepresentative => 'Sales Representative',
            self::Cashier => 'Cashier',
            self::InventoryClerk => 'Inventory Clerk',
            self::Accountant => 'Accountant',
            self::Auditor => 'Auditor',
            self::Viewer => 'Viewer',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Full access to everything',
            self::Administrator => 'Manages users, settings, products, warehouses',
            self::StoreManager => 'Runs daily operations, reports, inventory',
            self::WarehouseManager => 'Oversees warehouses, transfers, stock adjustments',
            self::WarehouseStaff => 'Receives, picks, transfers, counts stock',
            self::PurchasingOfficer => 'Purchase orders, suppliers, receiving goods',
            self::SalesRepresentative => 'Creates sales orders, views customers',
            self::Cashier => 'POS/counter sales, payments, receipts',
            self::InventoryClerk => 'Product maintenance, stock counts, inventory updates',
            self::Accountant => 'Payments, invoices, financial reports',
            self::Auditor => 'Read-only access to reports and logs',
            self::Viewer => 'Read-only access to permitted modules',
        };
    }

    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }

    public function isAdministrator(): bool
    {
        return $this === self::Administrator;
    }

    public function isAdmin(): bool
    {
        return $this === self::SuperAdmin || $this === self::Administrator;
    }
}
