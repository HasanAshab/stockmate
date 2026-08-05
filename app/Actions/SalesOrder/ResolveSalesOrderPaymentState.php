<?php

namespace App\Actions\SalesOrder;

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;

class ResolveSalesOrderPaymentState
{
    public function execute(SalesOrderStatus $status, array $payload): SalesOrder
    {
        $salesOrder = SalesOrder::where('transaction_reference', $payload['tran_id'])->sole();

        $salesOrder->update([
            'status' => $status,
            'payment_payload' => $payload,
        ]);

        return $salesOrder;
    }
}
