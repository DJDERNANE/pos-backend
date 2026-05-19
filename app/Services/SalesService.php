<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class SalesService
{
    public function listSales(User $user, ?string $storeId = null): Collection
    {
        $query = Sale::with(['saleItems.productVariant.product', 'saleItems.storeVariantPrice'])
            ->whereHas('store', function ($q) use ($user) {
                $q->whereIn('stores.id', $user->accessibleStores()->pluck('stores.id'));
            });

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->orderByDesc('created_at')->get();
    }

    public function findSale(string $id, User $user): Sale
    {
        $sale = Sale::with(['saleItems.productVariant.product', 'saleItems.storeVariantPrice'])->findOrFail($id);

        if (! $user->accessibleStores()->where('stores.id', $sale->store_id)->exists()) {
            throw new \Exception('Access denied to this sale.');
        }

        return $sale;
    }
}
