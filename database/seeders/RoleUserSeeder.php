<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Store;
use App\Models\User;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a single organization
        $org = Organization::factory()->create(['name' => 'Example Organization']);

        // Create two stores for that organization
        $stores = Store::factory()->count(2)->create(['organization_id' => $org->id]);

        // Create the organization owner (one user)
        User::factory()->owner($org)->create();

        // Create two cashiers, one assigned to each store
        foreach ($stores as $store) {
            User::factory()->cashier($store)->create();
        }
    }
}
