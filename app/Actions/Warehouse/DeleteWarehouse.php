<?php

namespace App\Actions\Warehouse;

use App\Exceptions\WarehouseNotEmpty;
use App\Models\Warehouse;

class DeleteWarehouse
{
    public function execute(Warehouse $warehouse): void
    {
        $hasStock = $warehouse->warehouseStocks()
            ->where('quantity', '>', 0)
            ->exists();

        if ($hasStock) {
            throw new WarehouseNotEmpty;
        }

        $warehouse->delete();
    }
}
