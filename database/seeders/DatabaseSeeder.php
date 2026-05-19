<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use Database\Seeders\CartSeeder;
use Database\Seeders\InventoryMovementSeeder;
use Database\Seeders\InventorySnapshotSeeder;
use Database\Seeders\SaleSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        // Seed the product catalog after stores are created
        $this->call(ProductCatalogSeeder::class);
        $this->call(CartSeeder::class);
        $this->call(SaleSeeder::class);
        $this->call(InventoryMovementSeeder::class);
        $this->call(InventorySnapshotSeeder::class);
    }
}
