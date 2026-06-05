<?php

namespace App\Actions\PurchaseOrder;

use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;

class UpdatePurchaseOrder
{
    public function execute(PurchaseOrder $purchaseOrder, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $data) {
            $purchaseOrder->update(collect($data)->except('items')->toArray());

            if (isset($data['items'])) {
                $purchaseOrder->items()->delete();

                foreach ($data['items'] as $item) {
                    $purchaseOrder->items()->create([
                        'product_id' => $item['product_id'],
                        'ordered_quantity' => $item['ordered_quantity'],
                        'unit_cost' => $item['unit_cost'],
                    ]);
                }
            }

            return $purchaseOrder;
        });
    }
}
