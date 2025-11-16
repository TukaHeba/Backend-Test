<?php

namespace App\Services;

use App\Jobs\SendOrderConfirmationEmail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderService
{
    /**
     * Get paginated orders for a user
     * If $userId is null, returns all orders
     */
    public function getOrders(?int $userId, int $perPage = 15): LengthAwarePaginator
    {
        try {
            $query = Order::with(['orderItems.product']);

            // customers can only view their own orders, admins can view all orders
            if ($userId !== null) {
                $query->where('user_id', $userId);
            }

            return $query->latest()->paginate($perPage);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve orders: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Get a single order
     */
    public function getOrder(Order $order): Order
    {
        try {
            return $order->load(['orderItems.product', 'user']);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve order: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Create a new order with database transaction
     *
     * @param  array  $items  Array of items with product_id and quantity
     */
    public function createOrder(int $userId, array $items): Order
    {
        try {
            return DB::transaction(function () use ($userId, $items) {
                // Extract product IDs from items
                $productIds = collect($items)->pluck('product_id');

                // Fetch and lock products to prevent race conditions
                $products = Product::whereIn('id', $productIds)
                    ->available()
                    ->select('id', 'name', 'price', 'quantity')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                // Validate all products exist and are available
                if ($products->count() !== $productIds->count()) {
                    throw new HttpException(400, 'One or more products are not available.');
                }

                // Create order with initial total of 0
                $order = Order::create([
                    'user_id' => $userId,
                    'total' => 0,
                    'status' => 'pending',
                ]);

                // Process each item and prepare order items
                $orderItems = [];
                foreach ($items as $item) {
                    $product = $products->get($item['product_id']);

                    // Validate quantity is available
                    if ($product->quantity < $item['quantity']) {
                        throw new HttpException(400, "Insufficient quantity for product: {$product->name}");
                    }

                    // Prepare order item data
                    $orderItems[] = [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                        'created_at' => now(),
                    ];

                    $product->decrement('quantity', $item['quantity']);
                }

                // Bulk insert all order items
                OrderItem::insert($orderItems);

                // Calculate and update order total from order items
                $order->update(['total' => $order->calculateTotalFromItems()]);

                // Dispatch email notification job
                dispatch(new SendOrderConfirmationEmail($order));

                return $order->load(['orderItems.product']);
            });
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to create order: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Cancel an order
     */
    public function cancelOrder(Order $order): Order
    {
        try {
            return DB::transaction(function () use ($order) {
                if ($order->isCancelled()) {
                    throw new HttpException(400, 'Order is already cancelled.');
                }

                if ($order->isCompleted()) {
                    throw new HttpException(400, 'Cannot cancel a completed order.');
                }

                foreach ($order->orderItems as $orderItem) {
                    $orderItem->product->increment('quantity', $orderItem->quantity);
                }

                $order->update(['status' => 'cancelled']);

                return $order->load(['orderItems.product']);
            });
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to cancel order: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }
}
