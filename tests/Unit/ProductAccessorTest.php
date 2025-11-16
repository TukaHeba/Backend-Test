<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductAccessorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /**
     * Test priceFormatted accessor formats price correctly.
     */
    public function test_price_formatted_accessor_formats_price_correctly(): void
    {
        $product = Product::first();
        $product->update(['price' => 99.99]);

        $formattedPrice = $product->price_formatted;

        $this->assertEquals('99.99 $', $formattedPrice);
    }
}
