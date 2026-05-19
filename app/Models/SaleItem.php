<?php

namespace App\Models;

use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'sale_id',
        'product_variant_id',
        'store_variant_price_id',
        'unit_type',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'unit_type' => UnitType::class,
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:4',
        'total_price' => 'decimal:4',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function storeVariantPrice(): BelongsTo
    {
        return $this->belongsTo(StoreVariantPrice::class);
    }
}
