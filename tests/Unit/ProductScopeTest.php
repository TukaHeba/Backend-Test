<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test active scope filters only active products.
     */
    public function test_active_scope_filters_active_products(): void
    {
        Product::where('id', '>', 0)->update(['status' => 'active']);
        Product::first()->update(['status' => 'inactive']);

        $activeProducts = Product::active()->get();

        foreach ($activeProducts as $product) {
            $this->assertEquals('active', $product->status);
        }
    }

    /**
     * Test available scope filters active and in stock products.
     */
    public function test_available_scope_filters_available_products(): void
    {
        $product = Product::first();
        $product->update(['status' => 'active', 'quantity' => 10]);

        $availableProducts = Product::available()->get();

        foreach ($availableProducts as $product) {
            $this->assertEquals('active', $product->status);
            $this->assertGreaterThan(0, $product->quantity);
        }
    }

    /**
     * Test category scope filters products by category.
     */
    public function test_category_scope_filters_products_by_category(): void
    {
        $category = Category::first();
        $product = Product::whereHas('categories', function ($q) use ($category) {
            $q->where('categories.id', $category->id);
        })->first();

        if (! $product) {
            $this->markTestSkipped('No products found in the first category');
        }

        $filteredProducts = Product::category($category->slug)->get();

        foreach ($filteredProducts as $product) {
            $this->assertTrue($product->categories->contains('id', $category->id));
        }
    }

    /**
     * Test inStock scope filters products with quantity > 0.
     */
    public function test_in_stock_scope_filters_products_in_stock(): void
    {
        Product::where('id', '>', 0)->update(['quantity' => 10]);
        Product::first()->update(['quantity' => 0]);

        $inStockProducts = Product::inStock()->get();

        foreach ($inStockProducts as $product) {
            $this->assertGreaterThan(0, $product->quantity);
        }
    }
}
