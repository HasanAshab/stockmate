<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\InteractsWithMedia;

#[Fillable('name', 'sku', 'price', 'reorder_threshold')]
class Product extends Model
{
    use HasFactory, InteractsWithMedia;

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('product_images')->singleFile();
    }

    public function registerMediaConversions(): void
    {
        $this->addMediaConversion('thumb')
            ->width(200)
            ->height(200);
    }

    #[Scope]
    protected function lowStock($query): void
    {
        $query->whereColumn('stock_quantity', '<=', 'reorder_threshold');
    }
}
