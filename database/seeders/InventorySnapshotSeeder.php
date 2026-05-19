<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\InventorySnapshot;
use App\Models\StoreVariantPrice;
use Illuminate\Database\Seeder;

class InventorySnapshotSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPrices = StoreVariantPrice::where('is_default', true)->get();

        foreach ($defaultPrices as $price) {
            InventorySnapshot::create([
                'store_id' => $price->store_id,
                'product_variant_id' => $price->product_variant_id,
                'quantity' => 100,
            ]);
        }
    }
}
