<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'organization_id',
        'name',
        'type',
        'phone',
        'address',
        'city',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function storeUsers()
    {
        return $this->hasMany(StoreUser::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'store_users')
            ->using(StoreUser::class)
            ->withPivot(['id', 'is_active'])
            ->withTimestamps();
    }
    // =========================================================================
    // PRICING RELATIONSHIPS  (new additions)
    // =========================================================================
 
    /**
     * All pricing records configured for this store.
     */
    public function storeVariantPrices(): HasMany
    {
        return $this->hasMany(StoreVariantPrice::class, 'store_id');
    }
 
    /**
     * Convenience: all prices for a specific variant in this store.
     */
    public function pricesForVariant(string $variantId): HasMany
    {
        return $this->storeVariantPrices()
            ->where('product_variant_id', $variantId);
    }
 
    /**
     * Convenience: the default price for a specific variant in this store.
     */
    public function defaultPriceForVariant(string $variantId): ?StoreVariantPrice
    {
        return $this->storeVariantPrices()
            ->where('product_variant_id', $variantId)
            ->where('is_default', true)
            ->first();
    }
}
