<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\VariantDTO;
use App\Http\Requests\Variant\VariantRequest;
use App\Http\Resources\VariantResource;
use App\Services\ProductVariantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductVariantController extends ApiController
{
    public function __construct(
        private ProductVariantService $variantService
    ) {}

    public function index(Request $request, string $productId): JsonResponse
    {
        try {
            $variants = $this->variantService->getForProduct($productId, $request->user());
            return $this->successResponse(
                VariantResource::collection($variants),
                'Variants retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function store(VariantRequest $request, string $productId): JsonResponse
    {
        try {
            $dto = VariantDTO::fromArray(array_merge($request->validated(), ['product_id' => $productId]));
            $variant = $this->variantService->create($dto, $request->user());

            return $this->successResponse(
                new VariantResource($variant),
                'Variant created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $variant = $this->variantService->findById($id, $request->user());
            return $this->successResponse(
                new VariantResource($variant),
                'Variant retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function update(VariantRequest $request, string $id): JsonResponse
    {
        try {
            $dto = VariantDTO::fromArray(array_merge($request->validated(), ['id' => $id]));
            $variant = $this->variantService->update($id, $dto, $request->user());

            return $this->successResponse(
                new VariantResource($variant),
                'Variant updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->variantService->delete($id, $request->user());
            return $this->successResponse([], 'Variant deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function setDefault(Request $request, string $productId, string $variantId): JsonResponse
    {
        try {
            $variant = $this->variantService->setDefault($productId, $variantId, $request->user());
            return $this->successResponse(
                new VariantResource($variant),
                'Variant set as default successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }
}
