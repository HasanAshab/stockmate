<?php

namespace App\Events;

use App\Models\WarehouseStock;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WarehouseStockLow
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly WarehouseStock $warehouseStock) {}
}
