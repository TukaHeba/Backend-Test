<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test that a user can create an order.
     */
    public function test_user_can_create_order(): void
    {
        $customer = User::where('role', 'customer')->first();
        Sanctum::actingAs($customer);

        $products = Product::available()->take(2)->get();

        if ($products->count() < 2) {
            $this->markTestSkipped('Not enough available products for testing');
        }

        $orderData = [
            'items' => [
                [
                    'product_id' => $products[0]->id,
                    'quantity' => 1,
                ],
                [
                    'product_id' => $products[1]->id,
                    'quantity' => 2,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'order_number',
                    'total',
                    'status',
                    'items',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Order created successfully',
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test that creating an order reduces product quantity.
     */
    public function test_order_reduces_product_quantity(): void
    {
        $customer = User::where('role', 'customer')->first();
        Sanctum::actingAs($customer);

        $product = Product::available()->first();

        if (! $product) {
            $this->markTestSkipped('No product with sufficient quantity found');
        }

        $originalQuantity = $product->quantity;
        $orderQuantity = 3;

        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => $orderQuantity,
                ],
            ],
        ];

        $response = $this->postJson('/api/v1/orders', $orderData);

        $response->assertStatus(201);

        $product->refresh();
        $this->assertEquals($originalQuantity - $orderQuantity, $product->quantity);
    }

    /**
     * Test that a user can view only their own orders.
     */
    public function test_user_can_view_only_their_orders(): void
    {
        $customer = User::where('role', 'customer')->first();
        Sanctum::actingAs($customer);

        // Create another customer
        $anotherCustomer = User::create([
            'name' => 'Another Customer',
            'email' => 'another@example.com',
            'password' => Hash::make('password@123'),
            'role' => 'customer',
        ]);

        // Create an order for the another customer
        $products = Product::available()->take(1)->get();

        if ($products->isEmpty()) {
            $this->markTestSkipped('No available products for testing');
        }

        Order::create([
            'user_id' => $anotherCustomer->id,
            'total' => 100.00,
            'status' => 'pending',
        ]);

        $response = $this->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination',
            ]);

        $orders = $response->json('data');
        $orderIds = collect($orders)->pluck('id')->toArray();

        // Verify all returned orders belong to the customer
        foreach ($orderIds as $orderId) {
            $this->assertDatabaseHas('orders', [
                'id' => $orderId,
                'user_id' => $customer->id,
            ]);
        }

        // Verify no orders from other customers are returned
        $otherCustomerOrders = Order::where('user_id', '!=', $customer->id)
            ->whereIn('id', $orderIds)
            ->count();
        $this->assertEquals(0, $otherCustomerOrders);
    }

    /**
     * Test that a user can cancel a pending order.
     */
    public function test_user_can_cancel_pending_order(): void
    {
        $customer = User::where('role', 'customer')->first();
        Sanctum::actingAs($customer);

        $product = Product::available()->first();

        if (! $product) {
            $this->markTestSkipped('No available products for testing');
        }

        // Create an order first
        $orderData = [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ];

        $createResponse = $this->postJson('/api/v1/orders', $orderData);
        $orderId = $createResponse->json('data.id');

        // Capture quantity after order creation
        $quantityAfterOrder = $product->fresh()->quantity;

        // Cancel the order
        $response = $this->patchJson("/api/v1/orders/{$orderId}/cancel");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order cancelled successfully',
            ]);

        $order = Order::find($orderId);
        $this->assertEquals('cancelled', $order->status);

        // Verify product quantity was restored
        $product->refresh();
        $this->assertEquals($quantityAfterOrder + 1, $product->quantity);
    }
}
