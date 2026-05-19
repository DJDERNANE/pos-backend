<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\InventorySnapshot;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StoreVariantPrice;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

                $variant = null;

                if (! empty($barcode)) {
                    $variant = ProductVariant::whereHas('barcodes', function ($q) use ($barcode) {
                        $q->where('barcode', $barcode);
                    })->first();
                }

                if (! $variant) {
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

                    if (! empty($barcode)) {
                        $variant->barcodes()->create(['barcode' => $barcode]);
                    }
                }

                $price = StoreVariantPrice::query()
                    ->where('store_id', $storeId)
                    ->where('product_variant_id', $variant->id)
                    ->where('unit_type', $unitType)
                    ->first();

                if ($price) {
                    $price->update([
                        'sell_price' => $sellPrice,
                        'buy_price' => $buyPrice ?? $price->buy_price,
                        'quantity_per_unit' => $quantityPerUnit,
                    ]);
                } else {
                    $price = StoreVariantPrice::create([
                        'store_id' => $storeId,
                        'product_variant_id' => $variant->id,
                        'unit_type' => $unitType,
                        'quantity_per_unit' => $quantityPerUnit,
                        'sell_price' => $sellPrice,
                        'buy_price' => $buyPrice,
                        'is_default' => $unitType === 'piece',
                    ]);
                }

                InventoryMovement::create([
                    'organization_id' => $price->store->organization_id,
                    'store_id' => $price->store_id,
                    'product_variant_id' => $variant->id,
                    'type' => 'purchase',
                    'quantity' => $quantity,
                    'direction' => 'in',
                    'reference_type' => 'bulk_import',
                    'reference_id' => null,
                    'unit_cost' => $buyPrice,
                    'created_by' => $user->id,
                    'notes' => 'Initial stock import or bulk inventory update.',
                ]);

                $stock = $this->calculateStock($price->store_id, $variant->id, $user);
                $this->refreshSnapshot($price->store_id, $variant->id, $stock);

                $processed[] = [
                    'product_name' => $name,
                    'barcode' => $barcode,
                    'variant_id' => $variant->id,
                    'added_quantity' => $quantity,
                    'new_total_quantity' => $stock,
                    'sell_price' => $sellPrice,
                ];
            }

            return $processed;
        });
    }

    public function getLedgerMovements(?string $storeId, ?string $variantId, ?string $type, User $user): Collection
    {
        $query = InventoryMovement::with(['productVariant.product', 'createdBy'])->orderByDesc('created_at');

        if ($storeId) {
            $this->verifyStoreAccess($storeId, $user);
            $query->where('store_id', $storeId);
        } else {
            $query->whereIn('store_id', $user->accessibleStores()->pluck('stores.id'));
        }

        if ($variantId) {
            $query->where('product_variant_id', $variantId);
        }

        if ($type) {
            $query->where('type', $type);
        }

        return $query->get();
    }

    public function calculateStock(string $storeId, string $variantId, User $user): float
    {
        $this->verifyStoreAccess($storeId, $user);

        return (float) InventoryMovement::where('store_id', $storeId)
            ->where('product_variant_id', $variantId)
            ->selectRaw('COALESCE(SUM(CASE WHEN direction = ? THEN quantity ELSE -quantity END), 0) as stock', ['in'])
            ->value('stock');
    }

    public function getLowStock(string $storeId, int $threshold, User $user): Collection
    {
        $this->verifyStoreAccess($storeId, $user);

        $positions = InventoryMovement::select('product_variant_id')
            ->selectRaw('SUM(CASE WHEN direction = ? THEN quantity ELSE -quantity END) as stock', ['in'])
            ->where('store_id', $storeId)
            ->groupBy('product_variant_id')
            ->having('stock', '<=', $threshold)
            ->get();

        $variantIds = $positions->pluck('product_variant_id')->all();
        $variants = ProductVariant::with(['product', 'barcodes'])
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        return $positions->map(function ($position) use ($variants) {
            return [
                'product_variant_id' => $position->product_variant_id,
                'stock' => (float)$position->stock,
                'variant' => $variants->get($position->product_variant_id),
            ];
        });
    }

    public function recordAdjustment(array $data, User $user): InventoryMovement
    {
        $this->verifyStoreAccess($data['store_id'], $user);

        $movement = InventoryMovement::create([
            'organization_id' => $this->resolveOrganizationId($data['store_id']),
            'store_id' => $data['store_id'],
            'product_variant_id' => $data['product_variant_id'],
            'type' => 'adjustment',
            'quantity' => (float)$data['quantity'],
            'direction' => $data['direction'],
            'reference_type' => $data['reference_type'] ?? 'adjustment',
            'reference_id' => $data['reference_id'] ?? null,
            'unit_cost' => isset($data['unit_cost']) ? (float)$data['unit_cost'] : null,
            'created_by' => $user->id,
            'notes' => $data['notes'] ?? null,
        ]);

        $stock = $this->calculateStock($movement->store_id, $movement->product_variant_id, $user);
        $this->refreshSnapshot($movement->store_id, $movement->product_variant_id, $stock);

        return $movement;
    }

    public function recordPurchase(array $data, User $user): InventoryMovement
    {
        $this->verifyStoreAccess($data['store_id'], $user);

        $movement = InventoryMovement::create([
            'organization_id' => $this->resolveOrganizationId($data['store_id']),
            'store_id' => $data['store_id'],
            'product_variant_id' => $data['product_variant_id'],
            'type' => 'purchase',
            'quantity' => (float)$data['quantity'],
            'direction' => 'in',
            'reference_type' => $data['reference_type'] ?? 'purchase',
            'reference_id' => $data['reference_id'] ?? null,
            'unit_cost' => (float)$data['unit_cost'],
            'created_by' => $user->id,
            'notes' => $data['notes'] ?? null,
        ]);

        $stock = $this->calculateStock($movement->store_id, $movement->product_variant_id, $user);
        $this->refreshSnapshot($movement->store_id, $movement->product_variant_id, $stock);

        return $movement;
    }

    private function resolveOrganizationId(string $storeId): string
    {
        return Store::query()
            ->where('id', $storeId)
            ->value('organization_id') ?? '';
    }

    private function refreshSnapshot(string $storeId, string $variantId, float $stock): void
    {
        try {
            InventorySnapshot::updateOrCreate(
                ['store_id' => $storeId, 'product_variant_id' => $variantId],
                ['quantity' => $stock]
            );
        } catch (\Throwable $exception) {
            Log::warning('Unable to refresh inventory snapshot: '.$exception->getMessage());
        }
    }

    private function verifyStoreAccess(string $storeId, User $user): void
    {
        if (! $user->accessibleStores()->where('stores.id', $storeId)->exists()) {
            throw new \Exception('Access denied to this store.');
        }
    }
}
