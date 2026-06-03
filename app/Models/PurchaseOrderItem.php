<?php

namespace App\Models;

use App\Http\Resources\PurchaseOrderItemResource;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable('purchase_order_id', 'product_id', 'ordered_quantity', 'received_quantity', 'unit_cost')]
class PurchaseOrderItem extends Model
{
    protected $attributes = [
        'received_quantity' => 0,
    ];

    protected function casts(): array
    {
        return [
            'unit_cost' => 'decimal:2',
        ];
    }

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

    public function toResource(): PurchaseOrderItemResource
    {
        return new PurchaseOrderItemResource($this);
    }
}
