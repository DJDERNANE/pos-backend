<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_variant_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
 
            $table->foreignUuid('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();
 
            $table->foreignUuid('product_variant_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();
 
            $table->enum('unit_type', ['piece', 'pack', 'box']);
            $table->unsignedInteger('quantity_per_unit')->nullable()->default(1);
 
            $table->decimal('sell_price', 12, 4);
            $table->decimal('buy_price', 12, 4)->nullable();
 
            $table->boolean('is_default')->default(false);
 
            $table->timestamps();
 
            // One price record per store + variant + unit combination
            $table->unique(
                ['store_id', 'product_variant_id', 'unit_type'],
                'svp_store_variant_unit_unique'
            );
 
            // Performance indexes for POS lookup patterns
            $table->index(['store_id', 'product_variant_id'], 'svp_store_variant_idx');
            $table->index(['store_id', 'product_variant_id', 'is_default'], 'svp_default_lookup_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_variant_prices');
    }
};
