<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable('purchase_order_id', 'product_id', 'ordered_quantity', 'received_quantity', 'unit_cost')]
class PurchaseOrderItem extends Model
{
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isFullyReceived(): bool
    {
        return $this->received_quantity >= $this->ordered_quantity;
    }
}
