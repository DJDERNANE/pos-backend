<?php

namespace App\Services;

use App\Enums\UnitType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Models\StoreVariantPrice;
use App\Models\User;

class CartItemService
{
    public function __construct(
        private CartService $cartService
    ) {
    }

    public function addItem(Cart $cart, ?string $storeVariantPriceId, ?string $productVariantId, ?UnitType $unitType, float $quantity): CartItem
    {
        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than zero.');
        }

        $price = $this->resolvePrice($cart->store_id, $storeVariantPriceId, $productVariantId, $unitType);

        $item = CartItem::where('cart_id', $cart->id)
            ->where('store_variant_price_id', $price->id)
            ->first();

        if ($item) {
            $item->quantity += $quantity;
            $item->unit_price = $price->sell_price;
            $item->total_price = $item->quantity * $price->sell_price;
            $item->save();

            return $item;
        }

        return CartItem::create([
            'cart_id' => $cart->id,
            'product_variant_id' => $price->product_variant_id,
            'store_variant_price_id' => $price->id,
            'unit_type' => $price->unit_type,
            'quantity' => $quantity,
            'unit_price' => $price->sell_price,
            'total_price' => $quantity * $price->sell_price,
        ]);
    }

    public function scanAndAdd(string $barcode, string $storeId, float $quantity, ?UnitType $unitType, User $user): CartItem
    {
        $variant = ProductVariant::whereHas('barcodes', function ($query) use ($barcode) {
            $query->where('barcode', $barcode);
        })->first();

        if (! $variant) {
            throw new \Exception('Product variant not found for barcode.');
        }

        $prices = StoreVariantPrice::where('store_id', $storeId)
            ->where('product_variant_id', $variant->id);

        if ($unitType) {
            $prices->where('unit_type', $unitType->value);
        } else {
            $prices->where('is_default', true);
        }

        $price = $prices->first();

        if (! $price) {
            throw new \Exception('No store price found for the scanned variant.');
        }

        $cart = $this->cartService->getActiveCart($storeId, $user);

        return $this->addItem($cart, $price->id, $variant->id, $unitType, $quantity);
    }

    public function updateItem(string $itemId, ?string $storeVariantPriceId, ?UnitType $unitType, float $quantity): CartItem
    {
        $item = CartItem::with(['cart', 'storeVariantPrice'])->findOrFail($itemId);

        if ($storeVariantPriceId) {
            $price = StoreVariantPrice::findOrFail($storeVariantPriceId);

            if ($price->store_id !== $item->cart->store_id) {
                throw new \Exception('Price record does not belong to the current store.');
            }

            $item->store_variant_price_id = $price->id;
            $item->product_variant_id = $price->product_variant_id;
            $item->unit_type = $price->unit_type;
            $item->unit_price = $price->sell_price;
        } elseif ($unitType) {
            $price = StoreVariantPrice::where('store_id', $item->cart->store_id)
                ->where('product_variant_id', $item->product_variant_id)
                ->where('unit_type', $unitType->value)
                ->first();

            if (! $price) {
                throw new \Exception('Unit type pricing not available for this variant.');
            }

            $item->store_variant_price_id = $price->id;
            $item->unit_type = $price->unit_type;
            $item->unit_price = $price->sell_price;
        }

        if ($quantity <= 0) {
            $item->delete();
            throw new \Exception('Cart item removed because quantity was set to zero.');
        }

        $item->quantity = $quantity;
        $item->total_price = $item->quantity * $item->unit_price;
        $item->save();

        return $item;
    }

    public function removeItem(string $itemId, User $user): void
    {
        $item = CartItem::with('cart')->findOrFail($itemId);

        if ($item->cart->user_id !== $user->id) {
            throw new \Exception('Cannot modify cart items for another user.');
        }

        $item->delete();
    }

    private function resolvePrice(string $storeId, ?string $storeVariantPriceId, ?string $productVariantId, ?UnitType $unitType): StoreVariantPrice
    {
        if ($storeVariantPriceId) {
            $price = StoreVariantPrice::with('productVariant')->findOrFail($storeVariantPriceId);

            if ($price->store_id !== $storeId) {
                throw new \Exception('Price record does not belong to the selected store.');
            }

            return $price;
        }

        if (! $productVariantId) {
            throw new \Exception('Either a store variant price or product variant must be provided.');
        }

        $query = StoreVariantPrice::where('store_id', $storeId)
            ->where('product_variant_id', $productVariantId);

        if ($unitType) {
            $query->where('unit_type', $unitType->value);
        } else {
            $query->where('is_default', true);
        }

        $price = $query->first();

        if (! $price) {
            throw new \Exception('No store price record found for this variant.');
        }

        return $price;
    }
}
