<?php

use App\Enums\UnitType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignUuid('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->foreignUuid('store_variant_price_id')->constrained('store_variant_prices')->restrictOnDelete();
            $table->enum('unit_type', UnitType::values());
            $table->decimal('quantity', 12, 4);
            $table->decimal('unit_price', 12, 4);
            $table->decimal('total_price', 14, 4);
            $table->timestamps();

            $table->index('cart_id');
            $table->index('product_variant_id');
            $table->index('store_variant_price_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
