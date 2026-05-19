<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\ScanCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\CartItemResource;
use App\Services\CartItemService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CartController extends ApiController
{
    public function __construct(
        private CartService $cartService,
        private CartItemService $cartItemService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $cart = $this->cartService->getActiveCart($request->input('store_id'), $request->user());

        return $this->successResponse(
            new CartResource($cart),
            'Cart retrieved successfully'
        );
    }

    public function scan(ScanCartRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $quantity = $validated['quantity'] ?? 1;

        $item = $this->cartItemService->scanAndAdd(
            $validated['barcode'],
            $validated['store_id'],
            $quantity,
            isset($validated['unit_type']) ? \App\Enums\UnitType::from($validated['unit_type']) : null,
            $request->user()
        );

        return $this->successResponse(
            new CartItemResource($item),
            'Item added to cart successfully',
            201
        );
    }

    public function addItem(AddToCartRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $cart = $this->cartService->getActiveCart($validated['store_id'], $request->user());

        $item = $this->cartItemService->addItem(
            $cart,
            $validated['store_variant_price_id'] ?? null,
            $validated['product_variant_id'] ?? null,
            isset($validated['unit_type']) ? \App\Enums\UnitType::from($validated['unit_type']) : null,
            (float)$validated['quantity']
        );

        return $this->successResponse(
            new CartItemResource($item),
            'Cart item added successfully',
            201
        );
    }

    public function updateItem(UpdateCartItemRequest $request, string $id): JsonResponse
    {
        $validated = $request->validated();

        $item = $this->cartItemService->updateItem(
            $id,
            $validated['store_variant_price_id'] ?? null,
            isset($validated['unit_type']) ? \App\Enums\UnitType::from($validated['unit_type']) : null,
            (float)$validated['quantity']
        );

        return $this->successResponse(
            new CartItemResource($item),
            'Cart item updated successfully'
        );
    }

    public function removeItem(string $id): JsonResponse
    {
        $this->cartItemService->removeItem($id, request()->user());

        return $this->successResponse([], 'Cart item removed successfully');
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->findActiveCart($request->input('store_id'), $request->user());

        if ($cart) {
            $this->cartService->clearCart($cart);
        }

        return $this->successResponse([], 'Cart cleared successfully');
    }
}
