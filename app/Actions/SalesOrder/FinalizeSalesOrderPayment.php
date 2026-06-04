<?php

namespace App\Actions;

use App\Enums\SalesOrderStatus;
use App\Enums\StockLogType;
use App\Models\SalesOrder;
use App\Models\WarehouseStock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinalizeSalesOrderPayment
{
    public function execute(string $trxId, array $payload): SalesOrder
    {
        $salesOrder = $this->getSalesOrder($trxId);

        if (! $salesOrder->status->isPending()) {
            return $salesOrder;
        }

        DB::transaction(function () use ($salesOrder, $payload) {
            $stocks = $this->getWarehouseStocks($salesOrder);
            $this->validateStocks($salesOrder, $stocks);
            $this->decrementStocks($salesOrder, $stocks);
            $this->createStockLogs($salesOrder);
            $this->markAsPaid($salesOrder, $payload);
        });

        return $salesOrder->refresh();
    }

    private function getSalesOrder(string $trxId): SalesOrder
    {
        return SalesOrder::query()
            ->where('transaction_id', $trxId)
            ->with([
                'items.product:id,name',
                'createdBy:id',
            ])
            ->firstOrFail();
    }

    private function getWarehouseStocks(SalesOrder $salesOrder): Collection
    {
        return WarehouseStock::query()
            ->where('warehouse_id', $salesOrder->warehouse_id)
            ->whereIn(
                'product_id',
                $salesOrder->items->pluck('product_id')
            )
            ->lockForUpdate()
            ->get()
            ->keyBy('product_id');
    }

    private function validateStocks(SalesOrder $salesOrder, Collection $stocks): void
    {
        foreach ($salesOrder->items as $item) {

            $stock = $stocks->get($item->product_id);

            if (! $stock || $stock->quantity < $item->quantity) {
                throw new RuntimeException(
                    "Insufficient stock for product {$item->product->name}"
                );
            }
        }
    }

    private function decrementStocks(SalesOrder $salesOrder, Collection $stocks): void
    {
        foreach ($salesOrder->items as $item) {

            $stocks
                ->get($item->product_id)
                ->decrement('quantity', $item->quantity);
        }
    }

    private function createStockLogs(SalesOrder $salesOrder): void
    {
        $now = now();

        $logs = $salesOrder->items
            ->map(fn ($item) => [
                'product_id'   => $item->product_id,
                'warehouse_id' => $salesOrder->warehouse_id,
                'type'         => StockLogType::Out,
                'quantity'     => $item->quantity,
                'unit_cost'    => $item->unit_price,
                'note'         => "Sold via Sales Order #{$salesOrder->id}",
                'created_at'   => $now,
                'updated_at'   => $now,
            ])
            ->toArray();

        $salesOrder->createdBy
            ->stockLogs()
            ->insert($logs);
    }

    private function markAsPaid(SalesOrder $salesOrder, array $payload): void
    {
        $salesOrder->update([
            'status'          => SalesOrderStatus::Paid,
            'payment_payload' => $payload,
        ]);
    }
}