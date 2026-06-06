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
            'roles' => Role::toArray(),
            'purchase_order_statuses' => PurchaseOrderStatus::toArray(),
            'sales_order_statuses' => SalesOrderStatus::toArray(),
            'stock_log_types' => StockLogType::toArray(),
        ];
    }
}
