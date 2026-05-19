<?php

namespace App\Services;

use App\DTOs\CheckoutDTO;
use App\Enums\InventoryMovementType;
use App\Enums\DirectionType;
use App\Enums\SaleStatus;
use App\Models\CartItem;
use App\Models\InventoryMovement;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(
        private CartService $cartService,
        private InventoryService $inventoryService,
    ) {
    }

    public function checkout(CheckoutDTO $dto, User $user): Sale
    {
        $cart = $this->cartService->findActiveCart($dto->store_id, $user);

        if (! $cart || $cart->cartItems->isEmpty()) {
            throw new \Exception('No active cart available for checkout.');
        }

        return DB::transaction(function () use ($dto, $user, $cart) {
            $store = Store::findOrFail($dto->store_id);
            $subtotal = $cart->cartItems->sum(fn (CartItem $item) => $item->total_price);
            $discountTotal = max(0.0, $dto->discount);
            $taxTotal = 0.0;
            $total = $subtotal - $discountTotal + $taxTotal;

            $sale = Sale::create([
                'organization_id' => $store->organization_id,
                'store_id' => $store->id,
                'user_id' => $user->id,
                'cart_id' => $cart->id,
                'invoice_number' => $this->buildInvoiceNumber($store->id),
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'total' => $total,
                'payment_method' => $dto->payment_method,
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

                InventoryMovement::create([
                    'organization_id' => $store->organization_id,
                    'store_id' => $store->id,
                    'product_variant_id' => $item->product_variant_id,
                    'type' => InventoryMovementType::SALE,
                    'quantity' => $item->quantity,
                    'direction' => DirectionType::OUT,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'unit_cost' => $item->storeVariantPrice?->buy_price,
                    'created_by' => $user->id,
                    'notes' => 'POS checkout sale',
                ]);
            }

            $this->cartService->completeCart($cart);
            $cart->cartItems()->delete();

            return $sale;
        });
    }

    private function buildInvoiceNumber(string $storeId): string
    {
        return 'INV-'.substr($storeId, 0, 8).'-'.strtoupper(Str::random(8));
    }
}
