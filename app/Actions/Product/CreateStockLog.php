<?php

namespace App\Actions\Product;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\StockLog;
use App\Models\User;
use App\Models\Product;
use App\Enums\StockLogType;

class CreateStockLog
{
    public function execute(User $user, array $data): StockLog
    {
        return DB::transaction(function () use ($user, $data) {
            $product = Product::query()
                ->lockForUpdate()
                ->findOrFail($data['product_id']);

            $type = StockLogType::from($data['type']);
            $quantity = $data['quantity'];

            $newQuantity = $type === StockLogType::In
                ? $product->quantity + $quantity
                : $product->quantity - $quantity;

            if ($newQuantity < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Not enough stock available.',
                ]);
            }

            $log = $user->stockLogs()->create($data);

            $product->update([
                'quantity' => $newQuantity,
            ]);

            return $log;
        });
    }
}
