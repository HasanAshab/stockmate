<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use App\DTO\SslcommerzPaymentPayload;
use App\Enums\SalesOrderStatus;

#[Fillable('customer_name', 'customer_email', 'customer_phone', 'warehouse_id', 'status', 'total_amount', 'transaction_id', 'payment_payload', 'created_by')]
class SalesOrder extends Model
{
    use HasFactory;

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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function generateTransactionId(): string
    {
        return $this->transaction_id = Str::ulid();
    }
}
