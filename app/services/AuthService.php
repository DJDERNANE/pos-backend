<?php

namespace App\Services;

use App\Models\User;
use App\Models\Organization;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(\App\DTOs\LoginDTO $dto): array
    {
        Log::info('Login attempt', ['email' => $dto->email]);

        $user = User::where('email', $dto->email)->first();

        if (!$user) {
            Log::warning('Login failed: User not found', ['email' => $dto->email]);
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        if (!Hash::check($dto->password, $user->password)) {
            Log::warning('Login failed: Invalid password', ['email' => $dto->email, 'user_id' => $user->id]);
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        Log::info('Login successful', ['email' => $dto->email, 'user_id' => $user->id]);

        $token = $user->createToken('auth')->plainTextToken;

        $orgRole = $user->organizationUsers()->first()?->role;

        $stores = $user->accessibleStores()->get();

        Log::info('Login token generated', ['user_id' => $user->id, 'org_role' => $orgRole, 'store_count' => $stores->count()]);

        return [
            'user' => $user,
            'token' => $token,
            'organization_role' => $orgRole,
            'stores' => $stores,
            'store_count' => $stores->count(),
            'redirect' => $orgRole === 'owner' ? 'select-store' : 'auto-store'
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
        Log::info('User logged out', ['user_id' => $user->id]);
    }

    public function switchStore(User $user, string $storeId): array
    {
        $store = $user->accessibleStores()->where('stores.id', $storeId)->first();

        if (!$store) {
            throw ValidationException::withMessages([
                'store_id' => ['You do not have access to this store.'],
            ]);
        }

        // In a POS SaaS, we might store this in session or just return it for the client to persist
        // Since it's a stateless API, we return the context
        Log::info('Store switched', ['user_id' => $user->id, 'store_id' => $storeId]);

        return [
            'current_store' => $store,
            'user' => $user,
        ];
    }
}