<?php

namespace App\Actions\SalesOrder;

use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateSalesOrder
{
    public function execute(array $data, int $userId): SalesOrder
    {
        return DB::transaction(function () use ($data, $userId) {

            $items = collect($data['items']);

            $products = $this->getProductsWithStock(
                $items,
                $data['warehouse_id'],
            );

            $this->validateStock($items, $products);

            $salesOrder = $this->createSalesOrder(
                $data,
                $userId,
                $this->calculateTotal($items),
            );

            $this->createItems($salesOrder, $items);

            return $salesOrder;
        });
    }

    private function getProductsWithStock(Collection $items, int $warehouseId): Collection
    {
        return Product::query()
            ->whereIn('id', $items->pluck('product_id'))
            ->with([
                'warehouseStocks' => fn ($query) => $query
                    ->where('warehouse_id', $warehouseId)
                    ->lockForUpdate(),
            ])
            ->get()
            ->keyBy('id');
    }

    private function validateStock(Collection $items, Collection $products): void
    {
        foreach ($items as $item) {
            $product = $products[$item['product_id']] ?? null;
            $stock = $product?->warehouseStocks->first();

            if (! $stock || $stock->quantity < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => [
                        "Insufficient stock for product {$product?->name}.",
                    ],
                ]);
            }
        }
    }

    private function calculateTotal(Collection $items): float
    {
        return $items->sum(
            fn ($item) => $item['quantity'] * $item['unit_price']
        );
    }

    private function createSalesOrder(array $data, int $userId, float $totalAmount): SalesOrder 
    {
        return SalesOrder::create([
            'customer_name' => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'customer_phone' => $data['customer_phone'],
            'warehouse_id' => $data['warehouse_id'],
            'total_amount' => $totalAmount,
            'created_by' => $userId,
        ]);
    }

    private function createItems(SalesOrder $salesOrder, Collection $items): void
    {
        $salesOrder->items()->insert(
            $items->map(fn ($item) => [
                'sales_order_id' => $salesOrder->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray()
        );
    }
}
