<?php

namespace App\Services;

use App\DTOs\CreateOrganizationDTO;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class OrganizationService
{
    public function getAllForUser(User $user): Collection
    {
        return $user->organizations()->get();
    }

    public function create(CreateOrganizationDTO $dto, User $user): Organization
    {
        return DB::transaction(function () use ($dto, $user) {
            $organization = Organization::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'currency_code' => $dto->currency_code,
                'timezone' => $dto->timezone,
            ]);

            // Assign user as owner
            $organization->users()->attach($user->id, ['role' => 'owner']);

            return $organization;
        });
    }

    public function findById(string $id, User $user): ?Organization
    {
        return $user->organizations()->where('organizations.id', $id)->first();
    }

    public function update(string $id, CreateOrganizationDTO $dto, User $user): Organization
    {
        $organization = $this->findById($id, $user);

        if (!$organization) {
            throw new \Exception('Organization not found or access denied.');
        }

        $organization->update([
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'currency_code' => $dto->currency_code,
            'timezone' => $dto->timezone,
        ]);

        return $organization;
    }
}
