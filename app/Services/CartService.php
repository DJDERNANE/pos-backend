<?php

namespace App\Services;

use App\Enums\CartStatus;
use App\Models\Cart;
use App\Models\Store;
use App\Models\User;

class CartService
{
    public function getActiveCart(string $storeId, User $user): Cart
    {
        $this->verifyStoreAccess($storeId, $user);

        return Cart::with(['cartItems.productVariant.product', 'cartItems.storeVariantPrice'])
            ->where('store_id', $storeId)
            ->where('user_id', $user->id)
            ->where('status', CartStatus::ACTIVE)
            ->firstOrCreate([
                'store_id' => $storeId,
                'user_id' => $user->id,
                'status' => CartStatus::ACTIVE,
            ]);
    }

    public function findActiveCart(string $storeId, User $user): ?Cart
    {
        $this->verifyStoreAccess($storeId, $user);

        return Cart::with(['cartItems.productVariant.product', 'cartItems.storeVariantPrice'])
            ->where('store_id', $storeId)
            ->where('user_id', $user->id)
            ->where('status', CartStatus::ACTIVE)
            ->first();
    }

    public function clearCart(Cart $cart): void
    {
        $cart->cartItems()->delete();
        $cart->status = CartStatus::ABANDONED;
        $cart->save();
    }

    public function completeCart(Cart $cart): void
    {
        $cart->status = CartStatus::CHECKED_OUT;
        $cart->save();
    }

    private function verifyStoreAccess(string $storeId, User $user): void
    {
        if (! $user->accessibleStores()->where('stores.id', $storeId)->exists()) {
            throw new \Exception('Access denied to this store.');
        }
    }
}
