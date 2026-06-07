<?php

namespace App\Observers;

use App\Events\WarehouseStockLow;
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

        WarehouseStockLow::dispatch($warehouseStock);
    }
}
