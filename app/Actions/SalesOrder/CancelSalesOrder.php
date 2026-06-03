<?php

namespace App\Actions\SalesOrder;

use App\Enums\SalesOrderStatus;
use App\Models\SalesOrder;
use Illuminate\Validation\ValidationException;

class CancelSalesOrder
{
    public function execute(SalesOrder $salesOrder): void
    {
        if ($salesOrder->status->isPaid()) {
            throw ValidationException::withMessages([
                'status' => 'Cannot cancel a paid order.',
            ]);
        }

        if (! $salesOrder->status->isPending()) {
            throw ValidationException::withMessages([
                'status' => 'Only pending sales orders can be cancelled.',
            ]);
        }

        $salesOrder->update([
            'status' => SalesOrderStatus::Cancelled,
        ]);
    }
}
