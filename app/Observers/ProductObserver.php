<?php

namespace App\Observers;

use App\Events\ProductStockLow;
use App\Models\Product;

class ProductObserver
{
    public function updated(Product $product): void
    {
        if (! $product->wasChanged('quantity')) {
            return;
        }

        $oldQuantity = $product->getOriginal('quantity');
        $newQuantity = $product->quantity;

        $crossedThreshold =
            $oldQuantity >= $product->reorder_threshold &&
            $newQuantity < $product->reorder_threshold;

        if (! $crossedThreshold) {
            return;
        }

        ProductStockLow::dispatch($product);
    }
}
