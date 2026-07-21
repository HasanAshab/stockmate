<?php

namespace App\Models;

use App\Enums\StockLogType;
use Database\Factories\StockLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable('product_id', 'warehouse_id', 'user_id', 'type', 'quantity', 'unit_cost', 'note')]
class StockLog extends Model
{
    /** @use HasFactory<StockLogFactory> */
    use HasFactory, LogsActivity, Searchable;

    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'type' => StockLogType::class,
            'unit_cost' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    #[SearchUsingPrefix(['id'])]
    #[SearchUsingFullText('note')]
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'note' => $this->note,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['product_id', 'type', 'quantity', 'note'])
            ->setDescriptionForEvent(fn (string $eventName) => "Stock was {$eventName}");
    }
}
