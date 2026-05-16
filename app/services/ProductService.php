<?php

namespace App\Services;

use App\DTOs\ProductDTO;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
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
        $product = Product::with('variants')->findOrFail($id);
        $this->verifyOrgAccess($product->organization_id, $user);
        
        return $product;
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
