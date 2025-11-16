<?php

namespace App\Http\Controllers\v1;

use App\Models\Product;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Cart\StoreCartItemRequest;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Display the user's cart.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $cart = $this->cartService->getCart(Auth::id());

        return self::success(new CartResource($cart), 'Cart retrieved successfully');
    }

    /**
     * Add a product to the cart.
     *
     * @param StoreCartItemRequest $request
     * @return JsonResponse
     */
    public function store(StoreCartItemRequest $request): JsonResponse
    {
        try {
            $product = Product::findOrFail($request->validated()['product_id']);
            $quantity = $request->validated()['quantity'];

            $cart = $this->cartService->addItem(Auth::id(), $product, $quantity);

            return self::success(new CartResource($cart), 'Product added to cart successfully', 201);
        } catch (\Exception $e) {
            return self::error(null, $e->getMessage(), 400);
        }
    }

    /**
     * Remove a product from the cart.
     *
     * @param CartItem $cartItem
     * @return JsonResponse
     */
    public function destroy(CartItem $cartItem): JsonResponse
    {
        $cart = $this->cartService->removeItem(Auth::id(), $cartItem);

        return self::success(new CartResource($cart), 'Product removed from cart successfully');
    }

    /**
     * Clear all items from the cart.
     *
     * @return JsonResponse
     */
    public function clear(): JsonResponse
    {
        $cart = $this->cartService->clearCart(Auth::id());

        return self::success(new CartResource($cart), 'Cart cleared successfully');
    }
}
