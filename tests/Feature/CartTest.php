<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test that cart calculates total correctly.
     */
    public function test_cart_calculates_total_correctly(): void
    {
        $customer = User::where('role', 'customer')->first();
        Sanctum::actingAs($customer);

        $products = Product::available()->take(2)->get();

        if ($products->count() < 2) {
            $this->markTestSkipped('Not enough available products for testing');
        }

        // Add first product to cart
        $this->postJson('/api/v1/cart', [
            'product_id' => $products[0]->id,
            'quantity' => 2,
        ]);

        // Add second product to cart
        $this->postJson('/api/v1/cart', [
            'product_id' => $products[1]->id,
            'quantity' => 3,
        ]);

        $response = $this->getJson('/api/v1/cart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'user_id',
                    'total',
                    'items',
                ],
            ]);

        $cartData = $response->json('data');
        $expectedTotal = ($products[0]->price * 2) + ($products[1]->price * 3);

        $this->assertEqualsWithDelta($expectedTotal, $cartData['total'], 0.01);
    }
}
