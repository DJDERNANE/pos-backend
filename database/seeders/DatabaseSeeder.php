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
        // Create organizations
        $org1 = Organization::factory()->create([
            'name' => 'Main Organization',
            'email' => 'org@example.com',
        ]);

        $org2 = Organization::factory()->create([
            'name' => 'Secondary Organization',
        ]);

        // Create stores
        $store1 = Store::factory()->create([
            'organization_id' => $org1->id,
            'name' => 'Store 1',
        ]);

        $store2 = Store::factory()->create([
            'organization_id' => $org1->id,
            'name' => 'Store 2',
        ]);

        $store3 = Store::factory()->create([
            'organization_id' => $org2->id,
            'name' => 'Store 3',
        ]);

        // Create test user
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Create organization user relationships
        OrganizationUser::factory()->create([
            'organization_id' => $org1->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);

        OrganizationUser::factory()->create([
            'organization_id' => $org2->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);

        // Create store user relationships
        StoreUser::factory()->create([
            'store_id' => $store1->id,
            'user_id' => $user->id,
        ]);

        StoreUser::factory()->create([
            'store_id' => $store2->id,
            'user_id' => $user->id,
        ]);

        StoreUser::factory()->create([
            'store_id' => $store3->id,
            'user_id' => $user->id,
        ]);
    }
}
