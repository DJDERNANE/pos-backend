<?php

namespace App\Services;

use App\DTOs\VariantDTO;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ProductVariantService
{
    public function getForProduct(string $productId, User $user): Collection
    {
        $product = Product::findOrFail($productId);
        $this->verifyOrgAccess($product->organization_id, $user);

        return $product->variants()->with('barcodes')->get();
    }

    public function create(VariantDTO $dto, User $user): ProductVariant
    {
        $product = Product::findOrFail($dto->product_id);
        $this->verifyOrgAccess($product->organization_id, $user);

        return DB::transaction(function () use ($dto, $product) {
            // If this is the first variant, it must be default
            $isFirst = $product->variants()->count() === 0;
            $isDefault = $isFirst || $dto->is_default;

            if ($isDefault) {
                // Remove default from others
                $product->variants()->update(['is_default' => false]);
            }

            return ProductVariant::create([
                'product_id' => $dto->product_id,
                'name' => $dto->name,
                'sku' => $dto->sku,
                'is_default' => $isDefault,
                'is_active' => $dto->is_active,
            ]);
        });
    }

    public function findById(string $id, User $user): ?ProductVariant
    {
        $variant = ProductVariant::with(['product', 'barcodes'])->findOrFail($id);
        $this->verifyOrgAccess($variant->product->organization_id, $user);
        
        return $variant;
    }

    public function update(string $id, VariantDTO $dto, User $user): ProductVariant
    {
        $variant = $this->findById($id, $user);
        
        return DB::transaction(function () use ($variant, $dto) {
            if ($dto->is_default && !$variant->is_default) {
                // Set others to false
                ProductVariant::where('product_id', $variant->product_id)
                    ->update(['is_default' => false]);
            }

            $variant->update([
                'name' => $dto->name,
                'sku' => $dto->sku,
                'is_default' => $dto->is_default || $variant->is_default, // Cannot unset default if it's the only one
                'is_active' => $dto->is_active,
            ]);

            return $variant;
        });
    }

    public function delete(string $id, User $user): void
    {
        $variant = $this->findById($id, $user);
        
        if ($variant->is_default) {
            throw new \Exception('Cannot delete default variant. Set another variant as default first.');
        }

        $variant->delete();
    }

    public function setDefault(string $productId, string $variantId, User $user): ProductVariant
    {
        $product = Product::findOrFail($productId);
        $this->verifyOrgAccess($product->organization_id, $user);

        return DB::transaction(function () use ($product, $variantId) {
            $product->variants()->update(['is_default' => false]);
            
            $variant = $product->variants()->findOrFail($variantId);
            $variant->update(['is_default' => true]);

            return $variant;
        });
    }

    private function verifyOrgAccess(string $organizationId, User $user): void
    {
        if (!$user->organizations()->where('organizations.id', $organizationId)->exists()) {
            throw new \Exception('Access denied to this organization.');
        }
    }
}
