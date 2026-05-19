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

    public function movements(Request $request): JsonResponse
    {
        $storeId = $request->query('store_id');
        $variantId = $request->query('product_variant_id');
        $type = $request->query('type');

        try {
            $movements = $this->inventoryService->getLedgerMovements($storeId, $variantId, $type, $request->user());
            return $this->successResponse(
                \App\Http\Resources\InventoryMovementResource::collection($movements),
                'Inventory movements retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function stock(Request $request, string $variantId): JsonResponse
    {
        $storeId = $request->query('store_id');

        if (! $storeId) {
            return $this->errorResponse('store_id is required', 400);
        }

        try {
            $stock = $this->inventoryService->calculateStock($storeId, $variantId, $request->user());
            return $this->successResponse(
                new \App\Http\Resources\InventoryStockResource(['store_id' => $storeId, 'product_variant_id' => $variantId, 'stock' => $stock]),
                'Stock retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|uuid|exists:stores,id',
            'product_variant_id' => 'required|uuid|exists:product_variants,id',
            'quantity' => 'required|numeric|min:0.0001',
            'direction' => 'required|in:in,out',
            'reference_type' => 'nullable|string|max:255',
            'reference_id' => 'nullable|uuid',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        try {
            $movement = $this->inventoryService->recordAdjustment($validated, $request->user());
            return $this->successResponse(new \App\Http\Resources\InventoryMovementResource($movement), 'Inventory adjusted successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $storeId = $request->query('store_id');
        $query = $request->query('q');

        if (!$storeId) {
            return $this->errorResponse('store_id is required', 400);
        }

        try {
            if ($query) {
                $inventory = $this->inventoryService->search($storeId, $query, $request->user());
                $message = 'Inventory search results';
            } else {
                $inventory = $this->inventoryService->getInventoryForStore($storeId, $request->user());
                $message = 'Inventory retrieved successfully';
            }

            return $this->successResponse(
                InventoryResource::collection($inventory),
                $message
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

    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|uuid|exists:stores,id',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.barcode' => 'nullable|string|max:255',
            'items.*.sell_price' => 'required|numeric|min:0',
            'items.*.buy_price' => 'nullable|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.sku' => 'nullable|string|max:255',
            'items.*.unit_type' => 'nullable|string|in:piece,pack,box',
            'items.*.quantity_per_unit' => 'nullable|integer|min:1',
        ]);

        try {
            $processed = $this->inventoryService->bulkAdd(
                $validated['store_id'],
                $validated['items'],
                $request->user()
            );

            return $this->successResponse(
                $processed,
                'Bulk items processed and added to inventory successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
