<?php

namespace App\Actions\Product;

use App\Enums\StockLogType;
use App\Models\StockLog;
use App\Models\User;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateStockLog
{
    public function execute(User $user, array $data): StockLog
    {
        return DB::transaction(function () use ($user, $data) {
            $warehouseStock = WarehouseStock::query()
                ->where('warehouse_id', $data['warehouse_id'])
                ->where('product_id', $data['product_id'])
                ->lockForUpdate()
                ->first();

            $type = StockLogType::from($data['type']);
            $quantity = $data['quantity'];

            if ($type->isIn()) {
                if (! $warehouseStock) {
                    $warehouseStock = WarehouseStock::create([
                        'warehouse_id' => $data['warehouse_id'],
                        'product_id' => $data['product_id'],
                        'quantity' => 0,
                        'reorder_threshold' => 10,
                    ]);
                }

                $newQuantity = $warehouseStock->quantity + $quantity;
            } else {
                if (! $warehouseStock || $warehouseStock->quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Not enough stock available in this warehouse.',
                    ]);
                }

                $newQuantity = $warehouseStock->quantity - $quantity;
            }

            $log = $user->stockLogs()->create($data);

            $warehouseStock->update([
                'quantity' => $newQuantity,
            ]);

            return $log;
        });
    }
}
