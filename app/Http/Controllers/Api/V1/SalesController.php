<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\SaleResource;
use App\Services\SalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesController extends ApiController
{
    public function __construct(
        private SalesService $salesService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $sales = $this->salesService->listSales($request->user(), $request->query('store_id'));

        return $this->successResponse(
            SaleResource::collection($sales),
            'Sales retrieved successfully'
        );
    }

    public function show(string $id): JsonResponse
    {
        $sale = $this->salesService->findSale($id, request()->user());

        return $this->successResponse(
            new SaleResource($sale),
            'Sale retrieved successfully'
        );
    }

    public function invoice(string $id): JsonResponse
    {
        $sale = $this->salesService->findSale($id, request()->user());

        return $this->successResponse(
            new SaleResource($sale),
            'Invoice retrieved successfully'
        );
    }
}
