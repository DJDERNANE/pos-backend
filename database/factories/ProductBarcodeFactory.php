<?php

namespace Database\Factories;

use App\Models\ProductBarcode;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductBarcode>
 */
class ProductBarcodeFactory extends Factory
{
    protected $model = ProductBarcode::class;

    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'barcode' => fake()->unique()->ean13(),
            'type' => fake()->optional()->randomElement(['EAN', 'internal', 'supplier']),
        ];
    }
}
