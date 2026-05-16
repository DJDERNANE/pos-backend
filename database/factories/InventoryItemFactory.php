<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    protected $model = InventoryItem::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'quantity' => fake()->randomFloat(4, 0, 200),
            'low_stock_alert' => fake()->optional()->numberBetween(1, 20),
            'is_active' => true,
        ];
    }
}
