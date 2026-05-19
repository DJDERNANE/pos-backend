<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CartStatus;
use App\Enums\UnitType;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Store;
use App\Models\StoreVariantPrice;
use App\Models\User;
use Illuminate\Database\Seeder;

class CartSeeder extends Seeder
{
    public function run(): void
    {
        $cashiers = User::where('email', 'like', 'cashier%@example.com')->get();
        $stores = Store::with('storeVariantPrices')->get();

        if ($stores->isEmpty() || $cashiers->isEmpty()) {
            return;
        }

        foreach ($stores as $index => $store) {
            $cashier = $cashiers[$index % $cashiers->count()];
            $defaultPrice = $store->storeVariantPrices()->where('is_default', true)->first();

            if (! $defaultPrice) {
                continue;
            }

            $activeCart = Cart::create([
                'store_id' => $store->id,
                'user_id' => $cashier->id,
                'status' => CartStatus::ACTIVE,
            ]);

            CartItem::create([
                'cart_id' => $activeCart->id,
                'product_variant_id' => $defaultPrice->product_variant_id,
                'store_variant_price_id' => $defaultPrice->id,
                'unit_type' => $defaultPrice->unit_type,
                'quantity' => 2,
                'unit_price' => $defaultPrice->sell_price,
                'total_price' => $defaultPrice->sell_price * 2,
            ]);

            $checkedOutCart = Cart::create([
                'store_id' => $store->id,
                'user_id' => $cashier->id,
                'status' => CartStatus::CHECKED_OUT,
            ]);

            CartItem::create([
                'cart_id' => $checkedOutCart->id,
                'product_variant_id' => $defaultPrice->product_variant_id,
                'store_variant_price_id' => $defaultPrice->id,
                'unit_type' => $defaultPrice->unit_type,
                'quantity' => 1,
                'unit_price' => $defaultPrice->sell_price,
                'total_price' => $defaultPrice->sell_price,
            ]);

            if ($index === 0) {
                Cart::create([
                    'store_id' => $store->id,
                    'user_id' => $cashier->id,
                    'status' => CartStatus::ABANDONED,
                ]);
            }
        }
    }
}
