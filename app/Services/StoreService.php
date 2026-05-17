<?php

namespace App\Services;

use App\DTOs\CreateStoreDTO;
use App\DTOs\AssignUserDTO;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class StoreService
{
    public function getAllForUser(User $user): Collection
    {
        return $user->accessibleStores()->get();
    }

    public function create(CreateStoreDTO $dto, User $user): Store
    {
        // Verify user belongs to the organization
        $organization = $user->organizations()->where('organizations.id', $dto->organization_id)->first();
        
        if (!$organization) {
            throw new \Exception('Organization not found or access denied.');
        }

        return DB::transaction(function () use ($dto, $user, $organization) {
            $store = Store::create([
                'organization_id' => $dto->organization_id,
                'name' => $dto->name,
                'type' => $dto->type,
                'phone' => $dto->phone,
                'address' => $dto->address,
                'city' => $dto->city,
            ]);

            // Assign creator as admin of the store
            $store->users()->attach($user->id, ['role' => 'admin']);

            return $store;
        });
    }

    public function findById(string $id, User $user): ?Store
    {
        return $user->accessibleStores()->where('stores.id', $id)->first();
    }

    public function update(string $id, CreateStoreDTO $dto, User $user): Store
    {
        $store = $this->findById($id, $user);

        if (!$store) {
            throw new \Exception('Store not found or access denied.');
        }

        $store->update([
            'name' => $dto->name,
            'type' => $dto->type,
            'phone' => $dto->phone,
            'address' => $dto->address,
            'city' => $dto->city,
        ]);

        return $store;
    }

    public function delete(string $id, User $user): void
    {
        $store = $this->findById($id, $user);

        if (!$store) {
            throw new \Exception('Store not found or access denied.');
        }

        $store->delete();
    }

    public function assignUser(string $storeId, AssignUserDTO $dto, User $admin): void
    {
        $store = $this->findById($storeId, $admin);

        if (!$store) {
            throw new \Exception('Store not found or access denied.');
        }

        // Check if user to be assigned belongs to the same organization
        $targetUser = User::findOrFail($dto->user_id);
        $isSameOrg = $targetUser->organizations()->where('organizations.id', $store->organization_id)->exists();

        if (!$isSameOrg) {
            throw new \Exception('User must belong to the organization before being assigned to a store.');
        }

        $store->users()->syncWithoutDetaching([
            $dto->user_id => ['role' => $dto->role]
        ]);
    }
}
