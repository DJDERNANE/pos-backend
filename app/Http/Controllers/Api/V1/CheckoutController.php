<?php

namespace App\Http\Controllers\Api\V1;

use App\DTOs\CheckoutDTO;
use App\Http\Requests\Checkout\CheckoutRequest;
use App\Http\Resources\SaleResource;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;

class CheckoutController extends ApiController
{
    public function __construct(
        private CheckoutService $checkoutService
    ) {
    }

    public function store(CheckoutRequest $request): JsonResponse
    {
        $dto = CheckoutDTO::fromArray($request->validated());
        $sale = $this->checkoutService->checkout($dto, $request->user());

        return $this->successResponse(
            new SaleResource($sale),
            'Checkout completed successfully',
            201
        );
    }
}
