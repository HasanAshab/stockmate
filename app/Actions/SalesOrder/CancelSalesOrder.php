<?php

namespace App\Actions\SalesOrder;

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;

class CancelSalesOrder
{
    public function execute(SalesOrder $salesOrder): void
    {
        $salesOrder->update([
            'status' => SalesOrderStatus::Cancelled,
        ]);
    }
}
