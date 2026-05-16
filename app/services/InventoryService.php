<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class InventoryService
{
    public function getInventoryForStore(string $storeId, User $user): Collection
    {
        $this->verifyStoreAccess($storeId, $user);

        return InventoryItem::where('store_id', $storeId)
            ->with(['variant.product', 'variant.barcodes'])
            ->get();
    }

    public function getInventoryForVariant(string $variantId, string $storeId, User $user): ?InventoryItem
    {
        $this->verifyStoreAccess($storeId, $user);

        return InventoryItem::where('store_id', $storeId)
            ->where('product_variant_id', $variantId)
            ->with(['variant.product', 'variant.barcodes'])
            ->first();
    }

    public function getLowStockItems(string $storeId, User $user): Collection
    {
        $this->verifyStoreAccess($storeId, $user);

        return InventoryItem::where('store_id', $storeId)
            ->whereColumn('quantity', '<=', 'low_stock_threshold')
            ->with(['variant.product', 'variant.barcodes'])
            ->get();
    }

    public function search(string $storeId, string $query, User $user): Collection
    {
        $this->verifyStoreAccess($storeId, $user);

        return InventoryItem::where('store_id', $storeId)
            ->whereHas('variant', function ($vq) use ($query) {
                $vq->where(function ($q) use ($query) {
                    $q->where('sku', 'like', "%{$query}%")
                        ->orWhereHas('product', function ($pq) use ($query) {
                            $pq->where('name', 'like', "%{$query}%");
                        })
                        ->orWhereHas('barcodes', function ($bq) use ($query) {
                            $bq->where('barcode', $query);
                        });
                });
            })
            ->with(['variant.product', 'variant.barcodes'])
            ->get();
    }

    private function verifyStoreAccess(string $storeId, User $user): void
    {
        if (!$user->accessibleStores()->where('stores.id', $storeId)->exists()) {
            throw new \Exception('Access denied to this store.');
        }
    }
}
