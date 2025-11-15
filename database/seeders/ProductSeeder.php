<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        
        $products = [
            ['name' => 'Laptop Computer', 'description' => 'High-performance laptop for work and gaming', 'price' => 999.99, 'quantity' => 15, 'status' => 'active'],
            ['name' => 'Wireless Mouse', 'description' => 'Ergonomic wireless mouse with long battery life', 'price' => 29.99, 'quantity' => 50, 'status' => 'active'],
            ['name' => 'Mechanical Keyboard', 'description' => 'RGB mechanical keyboard with blue switches', 'price' => 89.99, 'quantity' => 30, 'status' => 'active'],
            ['name' => 'Smartphone', 'description' => 'Latest smartphone with advanced camera', 'price' => 699.99, 'quantity' => 25, 'status' => 'active'],
            ['name' => 'Tablet', 'description' => '10-inch tablet perfect for reading and browsing', 'price' => 399.99, 'quantity' => 20, 'status' => 'active'],
            ['name' => 'Headphones', 'description' => 'Noise-cancelling wireless headphones', 'price' => 199.99, 'quantity' => 40, 'status' => 'active'],
            ['name' => 'T-Shirt', 'description' => 'Comfortable cotton t-shirt in various colors', 'price' => 19.99, 'quantity' => 100, 'status' => 'active'],
            ['name' => 'Jeans', 'description' => 'Classic fit denim jeans', 'price' => 49.99, 'quantity' => 75, 'status' => 'active'],
            ['name' => 'Sneakers', 'description' => 'Comfortable running shoes', 'price' => 79.99, 'quantity' => 60, 'status' => 'active'],
            ['name' => 'Winter Jacket', 'description' => 'Warm winter jacket with insulation', 'price' => 129.99, 'quantity' => 35, 'status' => 'active'],
            ['name' => 'Programming Book', 'description' => 'Learn Laravel framework from scratch', 'price' => 39.99, 'quantity' => 50, 'status' => 'active'],
            ['name' => 'Novel', 'description' => 'Bestselling fiction novel', 'price' => 14.99, 'quantity' => 80, 'status' => 'active'],
            ['name' => 'Cookbook', 'description' => 'Collection of delicious recipes', 'price' => 24.99, 'quantity' => 45, 'status' => 'active'],
            ['name' => 'Coffee Table', 'description' => 'Modern wooden coffee table', 'price' => 249.99, 'quantity' => 10, 'status' => 'active'],
            ['name' => 'Garden Tools Set', 'description' => 'Complete set of gardening tools', 'price' => 59.99, 'quantity' => 25, 'status' => 'active'],
            ['name' => 'Plant Pot', 'description' => 'Decorative ceramic plant pot', 'price' => 19.99, 'quantity' => 40, 'status' => 'active'],
            ['name' => 'Yoga Mat', 'description' => 'Non-slip yoga mat for exercise', 'price' => 34.99, 'quantity' => 55, 'status' => 'active'],
            ['name' => 'Dumbbells', 'description' => 'Set of adjustable dumbbells', 'price' => 89.99, 'quantity' => 20, 'status' => 'active'],
            ['name' => 'Basketball', 'description' => 'Official size basketball', 'price' => 24.99, 'quantity' => 30, 'status' => 'active'],
            ['name' => 'Tennis Racket', 'description' => 'Professional tennis racket', 'price' => 149.99, 'quantity' => 15, 'status' => 'active'],
        ];

        foreach ($products as $productData) {
            $product = Product::create($productData);
            
            // Attach product to 1-3 random categories
            $product->categories()->attach(
                $categories->random(rand(1, 3))->pluck('id')->toArray()
            );
        }
    }
}
