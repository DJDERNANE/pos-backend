<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {}

    /*
    |--------------------------------------------------------------------------
    | REGISTER OWNER
    |--------------------------------------------------------------------------
    */
    // public function register(RegisterRequest $request)
    // {
    //     $result = $this->authService->registerOwner(
    //         $request->validated()
    //     );

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Account created successfully',
    //         'data' => $result,
    //     ]);
    // }

    /*
    |--------------------------------------------------------------------------
    | LOGIN
    |--------------------------------------------------------------------------
    */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => $result,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */
    // public function logout(Request $request)
    // {
    //     $this->authService->logout($request->user());

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Logged out successfully',
    //     ]);
    // }

    /*
    |--------------------------------------------------------------------------
    | CURRENT USER (SESSION CONTEXT)
    |--------------------------------------------------------------------------
    */
    public function me(Request $request)
    {
        $user = $request->user();
        $stores = $user->accessibleStores();
        $orgRole = $user->organizationUsers()->first()?->role;

        return response()->json([
            'success' => true,
            'message' => 'User profile',

            'data' => [
                'user' => $user,

                'organization_role' => $orgRole,
                'organizations' => $user->organizations()->get(),

                'stores' => $stores,
                'store_count' => $stores->count(),

                'current_store_id' => session('current_store_id'),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SWITCH STORE (IMPORTANT FOR POS)
    |--------------------------------------------------------------------------
    */
    // public function switchStore(Request $request)
    // {
    //     $request->validate([
    //         'store_id' => ['required', 'uuid'],
    //     ]);

    //     $result = $this->authService->switchStore(
    //         $request->user(),
    //         $request->store_id
    //     );

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Store switched successfully',
    //         'data' => $result,
    //     ]);
    // }
}