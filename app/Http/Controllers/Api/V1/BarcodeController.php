<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\BarcodeDTO;
use App\Http\Requests\Barcode\BarcodeRequest;
use App\Http\Resources\BarcodeResource;
use App\Services\BarcodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BarcodeController extends ApiController
{
    public function __construct(
        private BarcodeService $barcodeService
    ) {}

    public function index(Request $request, string $variantId): JsonResponse
    {
        try {
            $barcodes = $this->barcodeService->getForVariant($variantId, $request->user());
            return $this->successResponse(
                BarcodeResource::collection($barcodes),
                'Barcodes retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function store(BarcodeRequest $request, string $variantId): JsonResponse
    {
        try {
            $dto = BarcodeDTO::fromArray(array_merge($request->validated(), ['product_variant_id' => $variantId]));
            $barcode = $this->barcodeService->create($dto, $request->user());

            return $this->successResponse(
                new BarcodeResource($barcode),
                'Barcode created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->barcodeService->delete($id, $request->user());
            return $this->successResponse([], 'Barcode deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 403);
        }
    }
}
