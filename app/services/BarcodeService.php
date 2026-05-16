<?php

namespace App\Services;

use App\DTOs\BarcodeDTO;
use App\Models\ProductBarcode;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class BarcodeService
{
    public function getForVariant(string $variantId, User $user): Collection
    {
        $variant = ProductVariant::with('product')->findOrFail($variantId);
        $this->verifyOrgAccess($variant->product->organization_id, $user);

        return $variant->barcodes()->get();
    }

    public function create(BarcodeDTO $dto, User $user): ProductBarcode
    {
        $variant = ProductVariant::with('product')->findOrFail($dto->product_variant_id);
        $this->verifyOrgAccess($variant->product->organization_id, $user);

        return ProductBarcode::create([
            'product_variant_id' => $dto->product_variant_id,
            'barcode' => $dto->barcode,
            'type' => $dto->type,
        ]);
    }

    public function delete(string $id, User $user): void
    {
        $barcode = ProductBarcode::with('variant.product')->findOrFail($id);
        $this->verifyOrgAccess($barcode->variant->product->organization_id, $user);

        $barcode->delete();
    }

    private function verifyOrgAccess(string $organizationId, User $user): void
    {
        if (!$user->organizations()->where('organizations.id', $organizationId)->exists()) {
            throw new \Exception('Access denied to this organization.');
        }
    }
}
