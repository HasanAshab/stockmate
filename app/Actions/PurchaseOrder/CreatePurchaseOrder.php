<?php

namespace App\Actions\PurchaseOrder;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreatePurchaseOrder
{
    public function execute(User $user, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($user, $data) {
            $purchaseOrder = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'note' => $data['note'] ?? null,
                'created_by' => $user->id,
            ]);

            foreach ($data['items'] as $item) {
                $purchaseOrder->items()->create([
                    'product_id' => $item['product_id'],
                    'ordered_quantity' => $item['ordered_quantity'],
                    'unit_cost' => $item['unit_cost'],
                ]);
            }

            return $purchaseOrder;
        });
    }
}
