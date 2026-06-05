<?php

namespace App\Actions\PurchaseOrder;

use App\Enums\PurchaseOrderStatus;
use App\Enums\StockLogType;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;

class ReceivePurchaseOrder
{
    public function execute(User $user, PurchaseOrder $purchaseOrder, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($user, $purchaseOrder, $items) {
            foreach ($items as $item) {
                $purchaseOrderItem = PurchaseOrderItem::with('product')->findOrFail($item['purchase_order_item_id']);

                $outstanding = $purchaseOrderItem->ordered_quantity - $purchaseOrderItem->received_quantity;

                if ($item['quantity_received'] > $outstanding) {
                    throw new \Exception("Received quantity exceeds outstanding quantity for product {$purchaseOrderItem->product->name}.");
                }

                $purchaseOrderItem->increment('received_quantity', $item['quantity_received']);

                $warehouseStock = WarehouseStock::firstOrCreate(
                    [
                        'warehouse_id' => $purchaseOrder->warehouse_id,
                        'product_id' => $purchaseOrderItem->product_id,
                    ],
                    ['quantity' => 0]
                );

                $warehouseStock->increment('quantity', $item['quantity_received']);

                $user->stockLogs()->create([
                    'product_id' => $purchaseOrderItem->product_id,
                    'warehouse_id' => $purchaseOrder->warehouse_id,
                    'type' => StockLogType::In,
                    'quantity' => $item['quantity_received'],
                    'unit_cost' => $purchaseOrderItem->unit_cost,
                    'note' => "Received from PO #{$purchaseOrder->id}",
                ]);
            }

            $allFullyReceived = $purchaseOrder->items()->get()->every(fn ($item) => $item->isFullyReceived());

            $purchaseOrder->update([
                'status' => $allFullyReceived ? PurchaseOrderStatus::Received : PurchaseOrderStatus::PartiallyReceived,
                'received_at' => $allFullyReceived ? now() : null,
            ]);

            return $purchaseOrder->fresh();
        });
    }
}
