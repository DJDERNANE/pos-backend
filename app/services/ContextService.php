<?php

namespace App\Services;

use App\Models\User;
use App\Models\Organization;
use App\Models\Store;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ContextService
{
    public static function getOrganization(User $user)
    {
        return $user->organizations()->first();
    }

    public static function getStore(User $user)
    {
        return $user->stores()->first();
    }
}