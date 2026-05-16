<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\LoginDTO;
use App\DTOs\RegisterDTO;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SwitchStoreRequest;
use App\Http\Resources\AuthTokenResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\StoreResource;
use App\Http\Resources\OrganizationResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $dto = LoginDTO::fromArray($request->validated());
            $result = $this->authService->login($dto);

            return $this->successResponse(
                new AuthTokenResource($result),
                'Login successful'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 401);
        }
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        // Implementation for registerOwner would go in AuthService
        // For now, let's assume it exists or I'll add it if needed
        // The user didn't specify register logic in AuthService yet
        return $this->errorResponse('Registration logic not implemented yet', 501);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse([], 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $stores = $user->accessibleStores()->get();
            $orgRole = $user->organizationUsers()->first()?->role;

            return $this->successResponse([
                'user' => new UserResource($user),
                'organization_role' => $orgRole,
                'organizations' => OrganizationResource::collection($user->organizations()->get()),
                'stores' => StoreResource::collection($stores),
                'store_count' => $stores->count(),
            ], 'User profile');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    public function switchStore(SwitchStoreRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->switchStore(
                $request->user(),
                $request->store_id
            );

            return $this->successResponse([
                'current_store' => new StoreResource($result['current_store']),
                'user' => new UserResource($result['user']),
            ], 'Store switched successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }
}
