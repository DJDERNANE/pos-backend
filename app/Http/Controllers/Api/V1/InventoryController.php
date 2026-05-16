<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\InventoryResource;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends ApiController
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $storeId = $request->query('store_id');

        if (!$storeId) {
            return $this->errorResponse('store_id is required', 400);
        }

        try {
            $inventory = $this->inventoryService->getInventoryForStore($storeId, $request->user());
            return $this->successResponse(
                InventoryResource::collection($inventory),
                'Inventory retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function show(Request $request, string $variantId): JsonResponse
    {
        $storeId = $request->query('store_id');

        if (!$storeId) {
            return $this->errorResponse('store_id is required', 400);
        }

        try {
            $item = $this->inventoryService->getInventoryForVariant($variantId, $storeId, $request->user());
            
            if (!$item) {
                return $this->errorResponse('Inventory item not found for this variant in this store', 404);
            }

            return $this->successResponse(
                new InventoryResource($item),
                'Inventory item retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function lowStock(Request $request): JsonResponse
    {
        $storeId = $request->query('store_id');

        if (!$storeId) {
            return $this->errorResponse('store_id is required', 400);
        }

        try {
            $items = $this->inventoryService->getLowStockItems($storeId, $request->user());
            return $this->successResponse(
                InventoryResource::collection($items),
                'Low stock items retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $storeId = $request->query('store_id');
        $query = $request->query('q', '');

        if (!$storeId) {
            return $this->errorResponse('store_id is required', 400);
        }

        try {
            $inventory = $this->inventoryService->search($storeId, $query, $request->user());
            return $this->successResponse(
                InventoryResource::collection($inventory),
                'Inventory search results'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }
}
