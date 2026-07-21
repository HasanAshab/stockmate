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

        WarehouseStockLow::dispatchIf($warehouseStock->isLow(), $warehouseStock);
    }
}
