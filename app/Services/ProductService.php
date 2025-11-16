<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ProductService
{
    /**
     * Get paginated products with filtering
     */
    public function getProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        try {
            $query = Product::query()->with('categories');

            // Filter by category
            $query->when(filled($filters['category'] ?? null), function ($q) use ($filters) {
                $q->category($filters['category']);
            });

            // Filter by price range
            $query->when(
                filled($filters['price_min'] ?? null) && filled($filters['price_max'] ?? null),
                function ($q) use ($filters) {
                    $q->priceRange($filters['price_min'], $filters['price_max']);
                },
                function ($q) use ($filters) {
                    $q->when(filled($filters['price_min'] ?? null), function ($q) use ($filters) {
                        $q->priceMin($filters['price_min']);
                    })
                        ->when(filled($filters['price_max'] ?? null), function ($q) use ($filters) {
                            $q->priceMax($filters['price_max']);
                        });
                }
            );

            // Filter by in stock or out of stock
            $query->when(filled($filters['in_stock'] ?? null), function ($q) use ($filters) {
                filter_var($filters['in_stock'], FILTER_VALIDATE_BOOLEAN)
                    ? $q->inStock()
                    : $q->outOfStock();
            });

            // search by name
            $query->when(filled($filters['search'] ?? null), function ($q) use ($filters) {
                $q->search($filters['search']);
            });

            return $query->paginate($perPage);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve products: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Get a single product
     */
    public function getProduct(Product $product): Product
    {
        try {
            return $product->load('categories');
        } catch (\Exception $e) {
            Log::error('Failed to retrieve product: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Create a new product
     */
    public function createProduct(array $data): Product
    {
        try {
            $categories = $data['categories'];
            unset($data['categories']);

            $product = Product::create($data);

            $product->categories()->sync($categories);

            return $product->load('categories');
        } catch (\Exception $e) {
            Log::error('Failed to create product: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Update a product
     */
    public function updateProduct(Product $product, array $data): Product
    {
        try {
            $categories = $data['categories'] ?? null;
            unset($data['categories']);

            $product->update((array_filter($data)));

            if ($categories !== null && is_array($categories)) {
                $product->categories()->sync($categories);
            }

            return $product->load('categories');
        } catch (\Exception $e) {
            Log::error('Failed to update product: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Delete a product (soft delete)
     */
    public function deleteProduct(Product $product): bool
    {
        try {
            return $product->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete product: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Get paginated trashed products
     */
    public function getTrashedProducts(int $perPage = 15): LengthAwarePaginator
    {
        try {
            return Product::onlyTrashed()->with('categories')
                ->paginate($perPage);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve trashed products: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Restore a soft-deleted product
     */
    public function restoreProduct(Product $product): bool
    {
        try {
            return $product->restore();
        } catch (\Exception $e) {
            Log::error('Failed to restore product: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Permanently delete a product
     */
    public function forceDeleteProduct(Product $product): bool
    {
        try {
            return $product->forceDelete();
        } catch (\Exception $e) {
            Log::error('Failed to force delete product: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }
}
