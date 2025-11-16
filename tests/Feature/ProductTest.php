<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test that an admin can create a product.
     */
    public function test_admin_can_create_product(): void
    {
        $admin = User::where('role', 'admin')->first();
        Sanctum::actingAs($admin);

        $category = Category::first();
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'quantity' => 10,
            'status' => 'active',
            'categories' => [$category->id],
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'quantity',
                    'status',
                    'slug',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully',
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'price' => 99.99,
        ]);
    }

    /**
     * Test that a customer cannot create a product.
     */
    public function test_customer_cannot_create_product(): void
    {
        $customer = User::where('role', 'customer')->first();
        Sanctum::actingAs($customer);

        $category = Category::first();
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product',
            'price' => 99.99,
            'quantity' => 10,
            'status' => 'active',
            'categories' => [$category->id],
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(403);
    }

    /**
     * Test that products can be filtered by category.
     */
    public function test_products_can_be_filtered_by_category(): void
    {
        $category = Category::first();
        $product = Product::whereHas('categories', function ($q) use ($category) {
            $q->where('categories.id', $category->id);
        })->first();

        if (! $product) {
            $this->markTestSkipped('No products found in the first category');
        }

        $response = $this->getJson("/api/v1/products?category={$category->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'pagination',
            ]);

        $products = $response->json('data');

        // Verify all products belong to the specified category
        foreach ($products as $productData) {
            $categoryIds = collect($productData['categories'] ?? [])->pluck('id')->toArray();
            $this->assertContains($category->id, $categoryIds);
        }
    }
}
