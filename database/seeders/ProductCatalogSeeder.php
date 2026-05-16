<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use the stores created by DatabaseSeeder
        $stores = Store::all();

        if ($stores->isEmpty()) {
            return;
        }

        // Create a larger set of products for the catalog
        $products = Product::factory()
            ->count(8)
            ->create();

        foreach ($products as $product) {
            // Default variant
            $defaultVariant = ProductVariant::factory()->create([
                'product_id' => $product->id,
                'name' => 'Default',
                'is_default' => true,
            ]);

            // Add a second variant
            $variant2 = ProductVariant::factory()->create([
                'product_id' => $product->id,
                'name' => 'Large',
            ]);

            // Add barcodes for each variant
            ProductBarcode::factory()->count(2)->create(['product_variant_id' => $defaultVariant->id]);
            ProductBarcode::factory()->count(1)->create(['product_variant_id' => $variant2->id]);

            // Create inventory items per store for each variant
            foreach ([$defaultVariant, $variant2] as $variant) {
                foreach ($stores as $store) {
                    InventoryItem::factory()->create([
                        'store_id' => $store->id,
                        'product_variant_id' => $variant->id,
                        'quantity' => fake()->randomFloat(4, 0, 100),
                        'low_stock_alert' => 5,
                    ]);
                }
            }
        }
    }
}
