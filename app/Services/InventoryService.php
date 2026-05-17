<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreVariantPrice;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function bulkAdd(string $storeId, array $items, User $user): array
    {
        $this->verifyStoreAccess($storeId, $user);

        return DB::transaction(function () use ($storeId, $items) {
            $processed = [];

            foreach ($items as $itemData) {
                $name = $itemData['name'];
                $barcode = $itemData['barcode'] ?? null;
                $sellPrice = (float) $itemData['sell_price'];
                $buyPrice = isset($itemData['buy_price']) ? (float) $itemData['buy_price'] : null;
                $quantity = (float) $itemData['quantity'];
                $sku = $itemData['sku'] ?? null;
                $unitType = $itemData['unit_type'] ?? 'piece';
                $quantityPerUnit = (int) ($itemData['quantity_per_unit'] ?? 1);

                // 1. Search for variant globally by barcode first
                $variant = null;
                if (!empty($barcode)) {
                    $variant = ProductVariant::whereHas('barcodes', function ($q) use ($barcode) {
                        $q->where('barcode', $barcode);
                    })->first();
                }

                // 2. If variant not found, create new global Product & Variant
                if (!$variant) {
                    $product = Product::create([
                        'name' => $name,
                        'description' => 'Imported via bulk invoice OCR',
                        'is_active' => true,
                    ]);

                    $variant = $product->variants()->create([
                        'name' => $name,
                        'sku' => $sku,
                        'is_default' => true,
                        'is_active' => true,
                    ]);

                    if (!empty($barcode)) {
                        $variant->barcodes()->create([
                            'barcode' => $barcode,
                        ]);
                    }
                }

                // 3. Handle Store Pricing
                $price = StoreVariantPrice::where('store_id', $storeId)
                    ->where('product_variant_id', $variant->id)
                    ->where('unit_type', $unitType)
                    ->first();

                if ($price) {
                    $price->update([
                        'sell_price' => $sellPrice,
                        'buy_price' => $buyPrice ?? $price->buy_price,
                    ]);
                } else {
                    StoreVariantPrice::create([
                        'store_id' => $storeId,
                        'product_variant_id' => $variant->id,
                        'unit_type' => $unitType,
                        'sell_price' => $sellPrice,
                        'buy_price' => $buyPrice,
                        'quantity_per_unit' => $quantityPerUnit,
                        'is_default' => $unitType === 'piece',
                    ]);
                }

                // 4. Handle Inventory Item
                $inventoryItem = InventoryItem::where('store_id', $storeId)
                    ->where('product_variant_id', $variant->id)
                    ->first();

                if ($inventoryItem) {
                    $inventoryItem->increment('quantity', $quantity);
                } else {
                    $inventoryItem = InventoryItem::create([
                        'store_id' => $storeId,
                        'product_variant_id' => $variant->id,
                        'quantity' => $quantity,
                        'low_stock_alert' => 5,
                    ]);
                }

                $processed[] = [
                    'product_name' => $name,
                    'barcode' => $barcode,
                    'variant_id' => $variant->id,
                    'added_quantity' => $quantity,
                    'new_total_quantity' => $inventoryItem->fresh()->quantity,
                    'sell_price' => $sellPrice,
                ];
            }

            return $processed;
        });
    }
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
            ->whereColumn('quantity', '<=', 'low_stock_alert')
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
