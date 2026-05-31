<?php

namespace App\Observers;

use App\Events\ProductStockLow;
use App\Models\WarehouseStock;

class WarehouseStockObserver
{
    public function updated(WarehouseStock $warehouseStock): void
    {
        if (! $warehouseStock->wasChanged('quantity')) {
            return;
        }

        $oldQuantity = $warehouseStock->getOriginal('quantity');
        $newQuantity = $warehouseStock->quantity;

        $crossedThreshold =
            $oldQuantity >= $warehouseStock->reorder_threshold &&
            $newQuantity < $warehouseStock->reorder_threshold;

        if (! $crossedThreshold) {
            return;
        }

        ProductStockLow::dispatch($warehouseStock->product);
    }
}
