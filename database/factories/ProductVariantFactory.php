<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->randomElement(['Default', 'Small', 'Medium', 'Large', '1L', '2L', 'Can']),
            'sku' => fake()->optional()->bothify('SKU-????-####'),
            'unit' => fake()->optional()->randomElement(['pcs', 'kg', 'g', 'ltr']),
            'quantity_value' => fake()->optional()->randomFloat(4, 1, 1000),
            'is_default' => false,
            'is_active' => true,
        ];
    }
}
