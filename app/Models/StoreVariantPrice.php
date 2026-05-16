<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string      $id
 * @property string      $store_id
 * @property string      $product_variant_id
 * @property UnitType    $unit_type
 * @property int         $quantity_per_unit
 * @property float       $sell_price
 * @property float|null  $buy_price
 * @property bool        $is_default
 *
 * @method static Builder forStore(string $storeId)
 * @method static Builder forVariant(string $variantId)
 * @method static Builder defaults()
 */
class StoreVariantPrice extends Model
{
    use HasUuids;

    protected $table = 'store_variant_prices';

    protected $fillable = [
        'store_id',
        'product_variant_id',
        'unit_type',
        'quantity_per_unit',
        'sell_price',
        'buy_price',
        'is_default',
    ];

    protected $casts = [
        'unit_type'         => UnitType::class,
        'quantity_per_unit' => 'integer',
        'sell_price'        => 'float',
        'buy_price'         => 'float',
        'is_default'        => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** Filter by store. */
    public function scopeForStore(Builder $query, string $storeId): Builder
    {
        return $query->where('store_id', $storeId);
    }

    /** Filter by product variant. */
    public function scopeForVariant(Builder $query, string $variantId): Builder
    {
        return $query->where('product_variant_id', $variantId);
    }

    /** Return only default price records. */
    public function scopeDefaults(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /** Filter by unit type. */
    public function scopeOfUnit(Builder $query, UnitType $unit): Builder
    {
        return $query->where('unit_type', $unit->value);
    }

    // -------------------------------------------------------------------------
    // Business helpers (pure, side-effect-free)
    // -------------------------------------------------------------------------

    /**
     * Calculate how many base units (pieces) this price record represents.
     */
    public function baseUnitCount(): int
    {
        return $this->quantity_per_unit ?? 1;
    }

    /**
     * Effective sell price per base unit.
     */
    public function sellPricePerBaseUnit(): float
    {
        $base = $this->baseUnitCount();

        return $base > 0 ? $this->sell_price / $base : $this->sell_price;
    }

    /**
     * Effective buy price per base unit, if known.
     */
    public function buyPricePerBaseUnit(): ?float
    {
        if ($this->buy_price === null) {
            return null;
        }

        $base = $this->baseUnitCount();

        return $base > 0 ? $this->buy_price / $base : $this->buy_price;
    }

    /**
     * Gross margin percentage, if buy price is present.
     */
    public function marginPercent(): ?float
    {
        if ($this->buy_price === null || $this->buy_price == 0) {
            return null;
        }

        return (($this->sell_price - $this->buy_price) / $this->buy_price) * 100;
    }

    // -------------------------------------------------------------------------
    // Model events — enforce single default per store + variant
    // -------------------------------------------------------------------------

    protected static function booted(): void
    {
        static::saving(function (self $price): void {
            if ($price->is_default) {
                self::query()
                    ->where('store_id', $price->store_id)
                    ->where('product_variant_id', $price->product_variant_id)
                    ->where('id', '!=', $price->id ?? '')   // exclude self on updates
                    ->update(['is_default' => false]);
            }
        });
    }
}