<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\SaleStatus;
use App\Models\Cart;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $cart = Cart::with('cartItems')->where('status', 'checked_out')->first();

        if (! $cart || $cart->cartItems->isEmpty()) {
            return;
        }

        $subtotal = $cart->cartItems->sum(fn ($item) => $item->total_price);
        $discountTotal = 0;
        $taxTotal = 0;
        $total = $subtotal - $discountTotal + $taxTotal;

        $sale = Sale::create([
            'organization_id' => $cart->store->organization_id,
            'store_id' => $cart->store_id,
            'user_id' => $cart->user_id,
            'cart_id' => $cart->id,
            'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'tax_total' => $taxTotal,
            'total' => $total,
            'payment_method' => 'cash',
            'status' => SaleStatus::COMPLETED,
        ]);

        foreach ($cart->cartItems as $item) {
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_variant_id' => $item->product_variant_id,
                'store_variant_price_id' => $item->store_variant_price_id,
                'unit_type' => $item->unit_type,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
            ]);
        }
    }
}
