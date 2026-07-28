<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SalesOrderStatus;
use App\Enums\StockLogType;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Configuration', 'APIs for retrieving system configuration and enums')]
#[Unauthenticated]
class ConfigController extends Controller
{
    /**
     * Get Enums
     *
     * Get all available enum values for various system entities.
     */
    #[Response(['purchase_order_statuses' => [['value' => 'draft', 'name' => 'Draft'], ['value' => 'ordered', 'name' => 'Ordered'], ['value' => 'received', 'name' => 'Received'], ['value' => 'cancelled', 'name' => 'Cancelled']], 'sales_order_statuses' => [['value' => 'pending', 'name' => 'Pending'], ['value' => 'paid', 'name' => 'Paid'], ['value' => 'cancelled', 'name' => 'Cancelled'], ['value' => 'failed', 'name' => 'Failed']], 'stock_log_types' => [['value' => 'in', 'name' => 'In'], ['value' => 'out', 'name' => 'Out'], ['value' => 'adjustment', 'name' => 'Adjustment'], ['value' => 'transfer', 'name' => 'Transfer']]], 200)]
    public function enums(): array
    {
        return [
            'purchase_order_statuses' => PurchaseOrderStatus::allCasesArray(),
            'sales_order_statuses' => SalesOrderStatus::allCasesArray(),
            'stock_log_types' => StockLogType::allCasesArray(),
        ];
    }
}
