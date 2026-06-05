<?php

namespace App\Actions\StockLog;

use App\Enums\StockLogType;
use App\Models\StockLog;
use App\Models\User;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TransferStock
{
    public function execute(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $sourceStock = WarehouseStock::query()
                ->where('warehouse_id', $data['from_warehouse_id'])
                ->where('product_id', $data['product_id'])
                ->lockForUpdate()
                ->first();

            if (! $sourceStock || $sourceStock->quantity < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Insufficient stock in source warehouse.',
                ]);
            }

            $sourceStock->decrement('quantity', $data['quantity']);

            $note = $data['note'] ?? null;
            $notePrefix = $note ? ": {$note}" : '';

            StockLog::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['from_warehouse_id'],
                'user_id' => $user->id,
                'type' => StockLogType::Out,
                'quantity' => $data['quantity'],
                'unit_cost' => null,
                'note' => "Transfer to warehouse #{$data['to_warehouse_id']}{$notePrefix}",
            ]);

            $destinationStock = WarehouseStock::firstOrCreate(
                [
                    'warehouse_id' => $data['to_warehouse_id'],
                    'product_id' => $data['product_id'],
                ],
                [
                    'quantity' => 0,
                    'reorder_threshold' => 10,
                ]
            );

            $destinationStock->increment('quantity', $data['quantity']);

            StockLog::create([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['to_warehouse_id'],
                'user_id' => $user->id,
                'type' => StockLogType::In,
                'quantity' => $data['quantity'],
                'unit_cost' => null,
                'note' => "Transfer from warehouse #{$data['from_warehouse_id']}{$notePrefix}",
            ]);
        });
    }
}
