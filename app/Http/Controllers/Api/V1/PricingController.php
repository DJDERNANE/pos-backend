<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\StorePriceDTO;
use App\Http\Requests\Pricing\StorePriceRequest;
use App\Http\Resources\StorePriceResource;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PricingController extends ApiController
{
    public function __construct(
        private PricingService $pricingService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $storeId = $request->query('store_id');

        if (!$storeId) {
            return $this->errorResponse('store_id is required', 400);
        }

        try {
            $prices = $this->pricingService->getPricesForStore($storeId, $request->user());
            return $this->successResponse(
                StorePriceResource::collection($prices),
                'Store prices retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function store(StorePriceRequest $request): JsonResponse
    {
        try {
            $dto = StorePriceDTO::fromArray($request->validated());
            $price = $this->pricingService->createOrUpdatePrice($dto, $request->user());

            return $this->successResponse(
                new StorePriceResource($price),
                'Store price saved successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $price = $this->pricingService->findById($id, $request->user());
            return $this->successResponse(
                new StorePriceResource($price),
                'Price retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function update(StorePriceRequest $request, string $id): JsonResponse
    {
        // Reuse store logic for update as it uses updateOrCreate
        return $this->store($request);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->pricingService->delete($id, $request->user());
            return $this->successResponse([], 'Price deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function scan(Request $request, string $barcode): JsonResponse
    {
        $storeId = $request->query('store_id');

        if (!$storeId) {
            return $this->errorResponse('store_id is required', 400);
        }

        try {
            $price = $this->pricingService->scanBarcode($barcode, $storeId, $request->user());

            if (!$price) {
                return $this->errorResponse('Item not found in this store', 404);
            }

            return $this->successResponse(
                new StorePriceResource($price),
                'Item scanned successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }
}
