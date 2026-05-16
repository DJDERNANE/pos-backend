<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\Store;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
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
