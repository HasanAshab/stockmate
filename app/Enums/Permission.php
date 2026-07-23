<?php

namespace App\Enums;

enum Permission: string
{
    // Dashboard
    case DashboardView = 'dashboard.view';

    // Products
    case ProductsView = 'products.view';
    case ProductsCreate = 'products.create';
    case ProductsUpdate = 'products.update';
    case ProductsDelete = 'products.delete';

    // Categories
    case CategoriesView = 'categories.view';
    case CategoriesCreate = 'categories.create';
    case CategoriesUpdate = 'categories.update';
    case CategoriesDelete = 'categories.delete';
    case CategoriesRestore = 'categories.restore';
    case CategoriesForceDelete = 'categories.force-delete';

    // Suppliers
    case SuppliersView = 'suppliers.view';
    case SuppliersCreate = 'suppliers.create';
    case SuppliersUpdate = 'suppliers.update';
    case SuppliersDelete = 'suppliers.delete';
    case SuppliersRestore = 'suppliers.restore';
    case SuppliersForceDelete = 'suppliers.force-delete';

    // Warehouses
    case WarehousesView = 'warehouses.view';
    case WarehousesCreate = 'warehouses.create';
    case WarehousesUpdate = 'warehouses.update';
    case WarehousesDelete = 'warehouses.delete';

    // Purchase Orders
    case PurchaseOrdersView = 'purchase-orders.view';
    case PurchaseOrdersCreate = 'purchase-orders.create';
    case PurchaseOrdersUpdate = 'purchase-orders.update';
    case PurchaseOrdersDelete = 'purchase-orders.delete';
    case PurchaseOrdersCancel = 'purchase-orders.cancel';
    case PurchaseOrdersMarkOrdered = 'purchase-orders.mark-ordered';
    case PurchaseOrdersReceive = 'purchase-orders.receive';

    // Sales Orders
    case SalesOrdersView = 'sales-orders.view';
    case SalesOrdersCreate = 'sales-orders.create';
    case SalesOrdersCancel = 'sales-orders.cancel';
    case SalesOrdersInitiatePayment = 'sales-orders.initiate-payment';

    // Stock Logs
    case StockLogsView = 'stock-logs.view';
    case StockLogsCreate = 'stock-logs.create';

    // Roles
    case RolesView = 'roles.view';
    case RolesManage = 'roles.manage';

    // Permissions
    case PermissionsView = 'permissions.view';
    case PermissionsManage = 'permissions.manage';
    
    // Users
    case UsersDeactivate = 'users.deactivate';
}