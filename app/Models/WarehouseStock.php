<?php

namespace App\Models;

use App\Observers\WarehouseStockObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Scope;

#[Fillable('warehouse_id', 'product_id', 'quantity', 'reorder_threshold')]
#[ObservedBy(WarehouseStockObserver::class)]
class WarehouseStock extends Model
{
    use HasFactory;

    protected $table = 'warehouse_stock';

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isLow(): bool
    {
        return $this->quantity <= $this->reorder_threshold;
    }

    #[Scope]
    protected function lowStock($query): void
    {
        $query->whereColumn('quantity', '<=', 'reorder_threshold');
    }
}
