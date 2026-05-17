<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\ProductDTO;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    public function __construct(
        private ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $organizationId = $request->query('organization_id');
        
        if (!$organizationId) {
            return $this->errorResponse('organization_id is required', 400);
        }

        try {
            $products = $this->productService->getAllForOrganization($organizationId, $request->user());
            return $this->successResponse(
                ProductResource::collection($products),
                'Products retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function store(ProductRequest $request): JsonResponse
    {
        try {
            $dto = ProductDTO::fromArray($request->validated());
            $product = $this->productService->create($dto, $request->user());

            return $this->successResponse(
                new ProductResource($product),
                'Product created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $product = $this->productService->findById($id, $request->user());
            return $this->successResponse(
                new ProductResource($product),
                'Product retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 404);
        }
    }

    public function update(ProductRequest $request, string $id): JsonResponse
    {
        try {
            $dto = ProductDTO::fromArray($request->validated());
            $product = $this->productService->update($id, $dto, $request->user());

            return $this->successResponse(
                new ProductResource($product),
                'Product updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->productService->delete($id, $request->user());
            return $this->successResponse([], 'Product deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function storeFull(Request $request): JsonResponse
    {
        try {
            $product = $this->productService->createFull($request->all(), $request->user());

            return $this->successResponse(
                new ProductResource($product),
                'Product and related records created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');

        try {
            $products = $this->productService->search($query, $request->user());
            return $this->successResponse(
                ProductResource::collection($products),
                'Products search results'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }
}
