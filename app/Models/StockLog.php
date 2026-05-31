<?php

namespace App\Models;

use App\Enums\StockLogType;
use Database\Factories\StockLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[Fillable('type', 'quantity', 'unit_cost', 'note')]
class StockLog extends Model
{
    /** @use HasFactory<StockLogFactory> */
    use HasFactory, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['product_id', 'type', 'quantity', 'note'])
            ->setDescriptionForEvent(fn (string $eventName) => "Stock was {$eventName}");
    }
}
