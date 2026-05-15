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
   public function login(array $data): array
    {
        Log::info('Login attempt', ['email' => $data['email']]);

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            Log::warning('Login failed: User not found', ['email' => $data['email']]);
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        if (!\Hash::check($data['password'], $user->password)) {
            Log::warning('Login failed: Invalid password', ['email' => $data['email'], 'user_id' => $user->id]);
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        Log::info('Login successful', ['email' => $data['email'], 'user_id' => $user->id]);

        $token = $user->createToken('auth')->plainTextToken;

        $orgRole = $user->organizationUsers()->first()?->role;

        $stores = $user->stores()->get();

        Log::info('Login token generated', ['user_id' => $user->id, 'org_role' => $orgRole, 'store_count' => $stores->count()]);

        return [
            'user' => $user,
            'token' => $token,

            'organization_role' => $orgRole,

            'stores' => $stores,
            'store_count' => $stores->count(),

            'redirect' => $orgRole === 'owner'
                ? 'select-store'
                : 'auto-store'
        ];
    }
}