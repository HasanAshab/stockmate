<?php

namespace App\Models;

use App\DTO\SslcommerzPaymentPayload;
use App\Enums\SalesOrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;

#[Fillable(
    'customer_name',
    'customer_email',
    'customer_phone',
    'warehouse_id',
    'status',
    'total_amount',
    'transaction_reference',
    'payment_payload',
    'creator_id'
)]
class SalesOrder extends Model
{
    use HasFactory, Searchable;

    protected $attributes = [
        'status' => SalesOrderStatus::Pending,
    ];

    protected function casts(): array
    {
        return [
            'status' => SalesOrderStatus::class,
            'payment_payload' => SslcommerzPaymentPayload::class,
            'total_amount' => 'decimal:2',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    #[SearchUsingPrefix(['id', 'transaction_reference'])]
    #[SearchUsingFullText(['customer_name', 'customer_email', 'customer_phone'])]
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'transaction_reference' => $this->transaction_reference,
        ];
    }

    public function generateTransactionId(): string
    {
        return $this->transaction_reference = Str::ulid();
    }
}
