<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\DirectionType;
use App\Enums\InventoryMovementType;
use App\Models\InventoryMovement;
use App\Models\Sale;
use App\Models\StoreVariantPrice;
use App\Models\User;
use Illuminate\Database\Seeder;

class InventoryMovementSeeder extends Seeder
{
    public function run(): void
    {
        $systemUser = User::where('email', 'owner@example.com')->first();
        $sales = Sale::all();
        $pricedVariants = StoreVariantPrice::with('store')->where('is_default', true)->get();

        if (! $systemUser || $pricedVariants->isEmpty()) {
            return;
        }

        foreach ($pricedVariants as $variantPrice) {
            InventoryMovement::create([
                'organization_id' => $variantPrice->store->organization_id,
                'store_id' => $variantPrice->store_id,
                'product_variant_id' => $variantPrice->product_variant_id,
                'type' => InventoryMovementType::PURCHASE,
                'quantity' => 100,
                'direction' => DirectionType::IN,
                'reference_type' => 'purchase',
                'reference_id' => null,
                'unit_cost' => $variantPrice->buy_price,
                'created_by' => $systemUser->id,
                'notes' => 'Initial stock purchase imported by seeder.',
            ]);
        }

        foreach ($sales as $sale) {
            foreach ($sale->saleItems as $item) {
                InventoryMovement::create([
                    'organization_id' => $sale->organization_id,
                    'store_id' => $sale->store_id,
                    'product_variant_id' => $item->product_variant_id,
                    'type' => InventoryMovementType::SALE,
                    'quantity' => $item->quantity,
                    'direction' => DirectionType::OUT,
                    'reference_type' => 'sale',
                    'reference_id' => $sale->id,
                    'unit_cost' => null,
                    'created_by' => $sale->user_id,
                    'notes' => 'Stock removed for POS sale.',
                ]);
            }
        }

        InventoryMovement::create([
            'organization_id' => $pricedVariants->first()->store->organization_id,
            'store_id' => $pricedVariants->first()->store_id,
            'product_variant_id' => $pricedVariants->first()->product_variant_id,
            'type' => InventoryMovementType::ADJUSTMENT,
            'quantity' => 5,
            'direction' => DirectionType::OUT,
            'reference_type' => 'adjustment',
            'reference_id' => null,
            'unit_cost' => null,
            'created_by' => $systemUser->id,
            'notes' => 'Manual inventory adjustment sample.',
        ]);
    }
}
