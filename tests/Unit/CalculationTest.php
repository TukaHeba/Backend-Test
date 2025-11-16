<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test total calculation for order.
     */
    public function test_order_calculates_total_from_items_correctly(): void
    {
        $user = User::first();
        $order = Order::create([
            'user_id' => $user->id,
            'total' => 0,
            'status' => 'pending',
        ]);

        $products = Product::take(2)->get();
        $quantities = [2, 3];
        $expectedTotal = 0;

        foreach ($products as $i => $product) {
            $quantity = $quantities[$i];
            $expectedTotal += $product->price * $quantity;

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
        }

        $this->assertEqualsWithDelta($expectedTotal, $order->calculateTotalFromItems(), 0.01);
    }
}
