<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderStatus;
use App\Enums\Role;
use App\Enums\SalesOrderStatus;
use App\Enums\StockLogType;

class ConfigController extends Controller
{
    public function enums(): array
    {
        return [
            'roles' => Role::allCasesArray(),
            'purchase_order_statuses' => PurchaseOrderStatus::allCasesArray(),
            'sales_order_statuses' => SalesOrderStatus::allCasesArray(),
            'stock_log_types' => StockLogType::allCasesArray(),
        ];
    }
}
