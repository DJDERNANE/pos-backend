<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'password' => static::$password ??= Hash::make('password'),
            'is_active' => true,
        ];
    }

    /**
     * Create an organization_user pivot for this user with the given role.
     */
    public function withOrganizationRole(string $role, ?\App\Models\Organization $organization = null): static
    {
        return $this->afterCreating(function (\App\Models\User $user) use ($role, $organization) {
            \App\Models\OrganizationUser::factory()->create([
                'user_id' => $user->id,
                'organization_id' => $organization?->id ?? \App\Models\Organization::factory(),
                'role' => $role,
                'is_active' => true,
            ]);
        });
    }

    /**
     * Attach the user to a store (creates StoreUser) and optionally set an organization role.
     */
    public function assignedToStore(?\App\Models\Store $store = null, bool $createOrganizationRelation = false, ?string $orgRole = null): static
    {
        return $this->afterCreating(function (\App\Models\User $user) use ($store, $createOrganizationRelation, $orgRole) {
            \App\Models\StoreUser::factory()->create([
                'user_id' => $user->id,
                'store_id' => $store?->id ?? \App\Models\Store::factory()->create()->id,
                'is_active' => true,
            ]);

            if ($createOrganizationRelation) {
                \App\Models\OrganizationUser::factory()->create([
                    'user_id' => $user->id,
                    'organization_id' => $store?->organization_id ?? \App\Models\Organization::factory(),
                    'role' => $orgRole ?? \App\Enums\UserRole::CASHIER->value,
                    'is_active' => true,
                ]);
            }
        });
    }

    public function owner(?\App\Models\Organization $organization = null): static
    {
        return $this->withOrganizationRole(\App\Enums\UserRole::OWNER->value, $organization);
    }

    public function admin(?\App\Models\Organization $organization = null): static
    {
        return $this->withOrganizationRole(\App\Enums\UserRole::ADMIN->value, $organization);
    }

    public function manager(?\App\Models\Organization $organization = null): static
    {
        return $this->withOrganizationRole(\App\Enums\UserRole::MANAGER->value, $organization);
    }

    public function cashier(?\App\Models\Store $store = null): static
    {
        return $this->assignedToStore($store, true, \App\Enums\UserRole::CASHIER->value);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
