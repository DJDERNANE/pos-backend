<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductVariant extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'unit',
        'quantity_value',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'quantity_value' => 'decimal:4',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function barcodes(): HasMany
    {
        return $this->hasMany(ProductBarcode::class);
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    // =========================================================================
    // PRICING RELATIONSHIPS  (new additions)
    // =========================================================================
 
    /**
     * All pricing records across every store for this variant.
     */
    public function storeVariantPrices(): HasMany
    {
        return $this->hasMany(StoreVariantPrice::class, 'product_variant_id');
    }
 
    /**
     * Convenience: prices scoped to a specific store.
     */
    public function pricesForStore(string $storeId): HasMany
    {
        return $this->storeVariantPrices()
            ->where('store_id', $storeId);
    }
 
    /**
     * Convenience: the single default price for a given store.
     */
    public function defaultPriceForStore(string $storeId): ?StoreVariantPrice
    {
        return $this->storeVariantPrices()
            ->where('store_id', $storeId)
            ->where('is_default', true)
            ->first();
    }
 
    /**
     * Convenience: price for a specific store + unit type combination.
     */
    public function priceForStoreAndUnit(string $storeId, UnitType $unit): ?StoreVariantPrice
    {
        return $this->storeVariantPrices()
            ->where('store_id', $storeId)
            ->where('unit_type', $unit->value)
            ->first();
    }
}
