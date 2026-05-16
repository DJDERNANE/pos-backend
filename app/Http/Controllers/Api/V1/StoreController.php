<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\CreateStoreDTO;
use App\DTOs\AssignUserDTO;
use App\Http\Requests\Store\StoreRequest;
use App\Http\Requests\Store\AssignUserRequest;
use App\Http\Resources\StoreResource;
use App\Services\StoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends ApiController
{
    public function __construct(
        private StoreService $storeService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $stores = $this->storeService->getAllForUser($request->user());

            return $this->successResponse(
                StoreResource::collection($stores),
                'Stores retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function store(StoreRequest $request): JsonResponse
    {
        try {
            $dto = CreateStoreDTO::fromArray($request->validated());
            $store = $this->storeService->create($dto, $request->user());

            return $this->successResponse(
                new StoreResource($store),
                'Store created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $store = $this->storeService->findById($id, $request->user());

            if (!$store) {
                return $this->errorResponse('Store not found', 404);
            }

            return $this->successResponse(
                new StoreResource($store),
                'Store retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function update(StoreRequest $request, string $id): JsonResponse
    {
        try {
            $dto = CreateStoreDTO::fromArray($request->validated());
            $store = $this->storeService->update($id, $dto, $request->user());

            return $this->successResponse(
                new StoreResource($store),
                'Store updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->storeService->delete($id, $request->user());
            return $this->successResponse([], 'Store deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function assignUser(AssignUserRequest $request, string $storeId): JsonResponse
    {
        try {
            $dto = AssignUserDTO::fromArray($request->validated());
            $this->storeService->assignUser($storeId, $dto, $request->user());

            return $this->successResponse([], 'User assigned to store successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
