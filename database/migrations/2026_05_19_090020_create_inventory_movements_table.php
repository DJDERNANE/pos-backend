<?php

use App\Enums\DirectionType;
use App\Enums\InventoryMovementType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('organization_id')->constrained('organizations')->restrictOnDelete();
            $table->foreignUuid('store_id')->constrained('stores')->restrictOnDelete();
            $table->foreignUuid('product_variant_id')->constrained('product_variants')->restrictOnDelete();
            $table->enum('type', InventoryMovementType::values());
            $table->decimal('quantity', 12, 4);
            $table->enum('direction', DirectionType::values());
            $table->string('reference_type')->nullable();
            $table->uuid('reference_id')->nullable();
            $table->decimal('unit_cost', 14, 4)->nullable();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('organization_id');
            $table->index('store_id');
            $table->index('product_variant_id');
            $table->index(['reference_type', 'reference_id']);
            $table->index('type');
            $table->index('direction');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
