<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;

#[Fillable('name', 'location', 'is_active')]
class Warehouse extends Model
{
    use HasFactory, Searchable;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function warehouseStocks(): HasMany
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function stocks(): HasMany
    {
        return $this->warehouseStocks();
    }

    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class);
    }

    #[SearchUsingPrefix(['id'])]
    #[SearchUsingFullText(['name', 'location'])]
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'location' => $this->location,
        ];
    }
}
