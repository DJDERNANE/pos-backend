<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UnitType;
use App\Models\InventoryItem;
use App\Models\Product;
use App\Models\ProductBarcode;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Models\StoreVariantPrice;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    /**
     * Buy price range per base unit in DZD — whole numbers only.
     * Covers everyday Algerian retail: ~50 DA (cheap consumable) to 8000 DA (mid-range product).
     */
    private const BUY_PRICE_MIN = 50;
    private const BUY_PRICE_MAX = 8_000;

    /**
     * Margin range applied on top of buy price to derive sell price.
     * e.g. 0.25 = 25 % markup → sell = buy × 1.25, then rounded to nearest 5 DA.
     */
    private const MARGIN_MIN = 0.10;  // 10 %
    private const MARGIN_MAX = 0.60;  // 60 %

    /**
     * DZD prices are rounded to the nearest 5 DA for clean POS display.
     * e.g. 127 DA → 125 DA,  133 DA → 135 DA,  250 DA → 250 DA
     */
    private const DZD_ROUNDING = 5;

    /** Number of pieces bundled in a PACK. */
    private const PACK_SIZE = 6;

    /** Number of pieces bundled in a BOX. */
    private const BOX_SIZE = 12;

    /** Per-unit sell price discount for buying a PACK vs individual pieces. */
    private const PACK_DISCOUNT_RATE = 0.05;  // 5 %

    /** Per-unit sell price discount for buying a BOX vs individual pieces. */
    private const BOX_DISCOUNT_RATE = 0.10;  // 10 %

    public function run(): void
    {
        $stores = Store::all();

        if ($stores->isEmpty()) {
            return;
        }

        $products = Product::factory()
            ->count(8)
            ->create();

        foreach ($products as $product) {
            // ── Variants ──────────────────────────────────────────────────
            $defaultVariant = ProductVariant::factory()->create([
                'product_id' => $product->id,
                'name'       => 'Default',
                'is_default' => true,
            ]);

            $variant2 = ProductVariant::factory()->create([
                'product_id' => $product->id,
                'name'       => 'Large',
            ]);

            // ── Barcodes ──────────────────────────────────────────────────
            ProductBarcode::factory()->count(2)->create(['product_variant_id' => $defaultVariant->id]);
            ProductBarcode::factory()->count(1)->create(['product_variant_id' => $variant2->id]);

            // ── Inventory + Pricing per store ─────────────────────────────
            foreach ([$defaultVariant, $variant2] as $variant) {
                // One cost base per variant — same supplier cost across all stores.
                // Sell margin is re-rolled per store to simulate regional pricing.
                $buyPricePerPiece = fake()->numberBetween(self::BUY_PRICE_MIN, self::BUY_PRICE_MAX);

                foreach ($stores as $store) {
                    InventoryItem::factory()->create([
                        'store_id'           => $store->id,
                        'product_variant_id' => $variant->id,
                        'quantity'           => fake()->randomFloat(4, 0, 100),
                        'low_stock_alert'    => 5,
                    ]);

                    $this->seedPricing($store->id, $variant->id, $buyPricePerPiece);
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Create PIECE (default), PACK, and BOX price records for one store+variant pair.
     *
     * All monetary values are whole DZD integers, rounded to the nearest 5 DA.
     */
    private function seedPricing(string $storeId, string $variantId, int $buyPricePerPiece): void
    {
        $margin            = fake()->randomFloat(2, self::MARGIN_MIN, self::MARGIN_MAX);
        $sellPricePerPiece = $this->dzd($buyPricePerPiece * (1 + $margin));

        // ── PIECE (default POS unit) ──────────────────────────────────────
        StoreVariantPrice::create([
            'store_id'           => $storeId,
            'product_variant_id' => $variantId,
            'unit_type'          => UnitType::PIECE,
            'quantity_per_unit'  => 1,
            'buy_price'          => $buyPricePerPiece,
            'sell_price'         => $sellPricePerPiece,
            'is_default'         => true,   // booted() event clears any prior default automatically
        ]);

        // ── PACK — 6 pieces, 5 % per-unit discount on sell side ──────────
        $packSellPerPiece = $this->dzd($sellPricePerPiece * (1 - self::PACK_DISCOUNT_RATE));

        StoreVariantPrice::create([
            'store_id'           => $storeId,
            'product_variant_id' => $variantId,
            'unit_type'          => UnitType::PACK,
            'quantity_per_unit'  => self::PACK_SIZE,
            'buy_price'          => $buyPricePerPiece * self::PACK_SIZE,
            'sell_price'         => $packSellPerPiece * self::PACK_SIZE,
            'is_default'         => false,
        ]);

        // ── BOX — 12 pieces, 10 % per-unit discount on sell side ─────────
        $boxSellPerPiece = $this->dzd($sellPricePerPiece * (1 - self::BOX_DISCOUNT_RATE));

        StoreVariantPrice::create([
            'store_id'           => $storeId,
            'product_variant_id' => $variantId,
            'unit_type'          => UnitType::BOX,
            'quantity_per_unit'  => self::BOX_SIZE,
            'buy_price'          => $buyPricePerPiece * self::BOX_SIZE,
            'sell_price'         => $boxSellPerPiece * self::BOX_SIZE,
            'is_default'         => false,
        ]);
    }

    /**
     * Round a raw calculated price to the nearest DZD_ROUNDING (5 DA) and return
     * a plain integer — no decimals, no fractions, clean POS display.
     *
     * Examples:
     *   dzd(127.4) → 125
     *   dzd(133.0) → 135
     *   dzd(250.0) → 250
     */
    private function dzd(float $raw): int
    {
        return (int) (round($raw / self::DZD_ROUNDING) * self::DZD_ROUNDING);
    }
}