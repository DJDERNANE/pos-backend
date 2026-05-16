<?php

namespace App\Services;

use App\DTOs\StorePriceDTO;
use App\Models\Store;
use App\Models\ProductVariant;
use App\Models\StoreVariantPrice;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PricingService
{
    public function getPricesForStore(string $storeId, User $user): Collection
    {
        $this->verifyStoreAccess($storeId, $user);

        return StoreVariantPrice::where('store_id', $storeId)
            ->with(['variant.product', 'variant.barcodes'])
            ->get();
    }

    public function createOrUpdatePrice(StorePriceDTO $dto, User $user): StoreVariantPrice
    {
        $this->verifyStoreAccess($dto->store_id, $user);

        // Verify variant belongs to the same organization as the store
        $store = Store::findOrFail($dto->store_id);
        $variant = ProductVariant::with('product')->findOrFail($dto->product_variant_id);
        
        if ($variant->product->organization_id !== $store->organization_id) {
            throw new \Exception('Product variant does not belong to the store\'s organization.');
        }

        return StoreVariantPrice::updateOrCreate(
            [
                'store_id' => $dto->store_id,
                'product_variant_id' => $dto->product_variant_id,
                'unit_type' => $dto->unit_type->value,
            ],
            [
                'price' => $dto->price,
                'cost_price' => $dto->cost_price,
                'quantity_per_unit' => $dto->quantity_per_unit,
                'is_active' => $dto->is_active,
            ]
        );
    }

    public function findById(string $id, User $user): StoreVariantPrice
    {
        $price = StoreVariantPrice::with(['variant.product', 'store'])->findOrFail($id);
        $this->verifyStoreAccess($price->store_id, $user);

        return $price;
    }

    public function delete(string $id, User $user): void
    {
        $price = $this->findById($id, $user);
        $price->delete();
    }

    public function scanBarcode(string $barcode, string $storeId, User $user): ?StoreVariantPrice
    {
        $this->verifyStoreAccess($storeId, $user);

        // Find variant by barcode first
        $variant = ProductVariant::whereHas('barcodes', function ($q) use ($barcode) {
            $q->where('barcode', $barcode);
        })->first();

        if (!$variant) {
            return null;
        }

        // Return the price for this variant in this store
        // If multiple units exist, return the base unit (piece) or the first one
        return StoreVariantPrice::where('store_id', $storeId)
            ->where('product_variant_id', $variant->id)
            ->with(['variant.product', 'variant.barcodes'])
            ->orderByRaw("CASE WHEN unit_type = 'piece' THEN 0 ELSE 1 END")
            ->first();
    }

    private function verifyStoreAccess(string $storeId, User $user): void
    {
        if (!$user->accessibleStores()->where('stores.id', $storeId)->exists()) {
            throw new \Exception('Access denied to this store.');
        }
    }
}
