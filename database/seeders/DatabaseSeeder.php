<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
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
        // Seed exactly one organization, two stores, one owner, two cashiers
        $org = Organization::factory()->create([
            'name' => 'Example Organization',
            'email' => 'owner@example.com',
        ]);

        $stores = Store::factory()->count(2)->create([
            'organization_id' => $org->id,
        ]);

        // Organization owner
        User::factory()->owner($org)->create([
            'name' => 'Organization Owner',
            'email' => 'owner@example.com',
        ]);

        // One cashier per store
        foreach ($stores as $i => $store) {
            User::factory()->cashier($store)->create([
                'name' => 'Cashier '.($i + 1),
                'email' => 'cashier'.($i + 1).'@example.com',
            ]);
        }
    }
}
