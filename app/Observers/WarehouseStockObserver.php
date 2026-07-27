<?php

namespace App\Observers;

use App\Events\ProductLowStock;
use App\Events\ProductOutOfStock;
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
        $threshold = $warehouseStock->low_stock_threshold;

        $wasLow = $oldQuantity <= $threshold;
        $isLow = $newQuantity <= $threshold;

        $wasOutOfStock = $oldQuantity === 0;
        $isOutOfStock = $newQuantity === 0;

        if (! $wasOutOfStock && $isOutOfStock) {
            ProductOutOfStock::dispatch($warehouseStock);
            return;
        }

        if (! $wasLow && $isLow) {
            ProductLowStock::dispatch($warehouseStock);
        }
    }
}
