<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::where('role', 'customer')->first();
        $products = Product::all();
        $statuses = ['pending', 'processing', 'completed', 'cancelled'];

        // Create 5 orders for the customer
        for ($i = 0; $i < 5; $i++) {
            $order = Order::create([
                'user_id' => $customer->id,
                'total' => 0,
                'status' => $statuses[array_rand($statuses)],
            ]);

            // Add 2-5 random products to order
            $orderProducts = $products->random(rand(2, 5));
            $total = 0;

            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $total += $product->price * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);
            }

            $order->update(['total' => $total]);
        }
    }
}
