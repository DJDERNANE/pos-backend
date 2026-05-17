<?php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function createFull(array $data, User $user): Product
    {
        return DB::transaction(function () use ($data, $user) {
            // 1. Create Product
            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // If no variants provided, create a default one from top-level fields
            $variantsData = $data['variants'] ?? [
                [
                    'name' => $data['name'],
                    'sku' => $data['sku'] ?? null,
                    'barcode' => $data['barcode'] ?? null,
                    'sell_price' => $data['sell_price'] ?? null,
                    'buy_price' => $data['buy_price'] ?? null,
                    'quantity' => $data['quantity'] ?? null,
                    'is_default' => true,
                ]
            ];

            foreach ($variantsData as $vData) {
                // 2. Create Variant
                $variant = $product->variants()->create([
                    'name' => $vData['name'],
                    'sku' => $vData['sku'] ?? null,
                    'unit' => $vData['unit'] ?? null,
                    'quantity_value' => $vData['quantity_value'] ?? null,
                    'is_default' => $vData['is_default'] ?? false,
                ]);

                // 3. Create Barcodes
                $barcodes = $vData['barcodes'] ?? [];
                if (isset($vData['barcode'])) {
                    $barcodes[] = ['barcode' => $vData['barcode']];
                }

                foreach ($barcodes as $bData) {
                    $variant->barcodes()->create([
                        'barcode' => $bData['barcode'],
                        'type' => $bData['type'] ?? null,
                    ]);
                }

                // 4. Create Prices & Inventory if store_id provided
                if (isset($data['store_id'])) {
                    $prices = $vData['prices'] ?? [];
                    
                    // If no explicit prices but top-level sell/buy provided, create a 'piece' price
                    if (empty($prices) && (isset($vData['sell_price']) || isset($vData['buy_price']))) {
                        $prices[] = [
                            'unit_type' => 'piece',
                            'sell_price' => $vData['sell_price'] ?? 0,
                            'buy_price' => $vData['buy_price'] ?? null,
                            'quantity_per_unit' => 1,
                            'is_default' => true,
                        ];
                    }

                    foreach ($prices as $pData) {
                        $variant->storeVariantPrices()->create([
                            'store_id' => $data['store_id'],
                            'unit_type' => $pData['unit_type'],
                            'sell_price' => $pData['sell_price'],
                            'buy_price' => $pData['buy_price'] ?? null,
                            'quantity_per_unit' => $pData['quantity_per_unit'] ?? 1,
                            'is_default' => $pData['is_default'] ?? false,
                        ]);
                    }

                    // 5. Create Inventory
                    $invData = $vData['inventory'] ?? ['quantity' => $vData['quantity'] ?? 0];
                    $variant->inventoryItems()->create([
                        'store_id' => $data['store_id'],
                        'quantity' => $invData['quantity'],
                        'low_stock_alert' => $invData['low_stock_alert'] ?? 5,
                    ]);
                }
            }

            return $product->load('variants.barcodes', 'variants.storeVariantPrices');
        });
    }

    public function getAllForOrganization(string $organizationId, User $user): Collection
    {
        // Verify user belongs to org
        $this->verifyOrgAccess($organizationId, $user);

        return Product::where('organization_id', $organizationId)->with('variants')->get();
    }

    public function create(ProductDTO $dto, User $user): Product
    {
        $this->verifyOrgAccess($dto->organization_id, $user);

        return Product::create([
            'organization_id' => $dto->organization_id,
            'name' => $dto->name,
            'description' => $dto->description,
            'category' => $dto->category,
            'is_active' => $dto->is_active,
        ]);
    }

    public function findById(string $id, User $user): ?Product
    {
        return Product::with('variants')->findOrFail($id);
    }

    public function update(string $id, ProductDTO $dto, User $user): Product
    {
        $product = $this->findById($id, $user);
        
        $product->update([
            'name' => $dto->name,
            'description' => $dto->description,
            'category' => $dto->category,
            'is_active' => $dto->is_active,
        ]);

        return $product;
    }

    public function delete(string $id, User $user): void
    {
        $product = $this->findById($id, $user);
        $product->delete();
    }

    public function search(string $query, User $user): Collection
    {
        return Product::where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhereHas('variants', function ($vq) use ($query) {
                        $vq->where('sku', 'like', "%{$query}%")
                            ->orWhereHas('barcodes', function ($bq) use ($query) {
                                $bq->where('barcode', $query);
                            });
                    });
            })
            ->with(['variants.barcodes'])
            ->get();
    }

    private function verifyOrgAccess(string $organizationId, User $user): void
    {
        if (!$user->organizations()->where('organizations.id', $organizationId)->exists()) {
            throw new \Exception('Access denied to this organization.');
        }
    }
}
