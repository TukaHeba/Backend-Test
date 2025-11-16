<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CartService
{
    /**
     * Helper method to get or create cart for user
     */
    public function getOrCreateCart(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    /**
     * Get user's cart with items
     */
    public function getCart(int $userId): Cart
    {
        try {
            $cart = $this->getOrCreateCart($userId);

            return $cart->load(['cartItems.product']);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve cart: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Add item to cart
     */
    public function addItem(int $userId, Product $product, int $quantity): Cart
    {
        try {
            return DB::transaction(function () use ($userId, $product, $quantity) {
                // Check if product is available
                if (! $product->available()) {
                    throw new \Exception('Product is not available or out of stock.');
                }

                // Check if item already exists in cart
                $cartItem = CartItem::userCartItems($userId)
                    ->where('product_id', $product->id)
                    ->first();

                if ($cartItem) {
                    // Update quantity and price
                    $newQuantity = $cartItem->quantity + $quantity;

                    if ($product->quantity < $newQuantity) {
                        throw new \Exception('Insufficient quantity available.');
                    }

                    $cartItem->update([
                        'quantity' => $newQuantity,
                        'price' => $product->price,
                    ]);
                } else {
                    // Get or create cart first
                    $cart = $this->getOrCreateCart($userId);

                    // Create new cart item
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $product->price,
                    ]);
                }

                return $this->getCart($userId);
            });
        } catch (\Exception $e) {
            Log::error('Failed to add item to cart: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(int $userId, CartItem $cartItem): Cart
    {
        try {
            $cartItem->load('cart');

            // Verify cart item belongs to user's cart
            if (! $cartItem->cart || $cartItem->cart->user_id !== $userId) {
                throw new HttpException(403, 'You are not authorized to remove this item.');
            }

            $cartItem->delete();

            return $this->getCart($userId);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to remove item from cart: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Clear all items from cart
     */
    public function clearCart(int $userId): Cart
    {
        try {
            CartItem::userCartItems($userId)->delete();

            return $this->getCart($userId);
        } catch (\Exception $e) {
            Log::error('Failed to clear cart: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }
}
