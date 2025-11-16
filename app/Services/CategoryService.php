<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    /**
     * Get paginated categories
     */
    public function getCategories(int $perPage = 15): LengthAwarePaginator
    {
        try {
            $cacheKey = "categories:list:per_page:{$perPage}";

            return Cache::remember($cacheKey, 3600, function () use ($perPage) {
                return Category::query()
                    ->with('products')
                    ->paginate($perPage);
            });
        } catch (\Exception $e) {
            Log::error('Failed to retrieve categories: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Get a single category
     */
    public function getCategory(Category $category): Category
    {
        try {
            $cacheKey = "category:{$category->id}";

            return Cache::remember($cacheKey, 3600, function () use ($category) {
                return $category->load('products');
            });
        } catch (\Exception $e) {
            Log::error('Failed to retrieve category: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Create a new category
     */
    public function createCategory(array $data): Category
    {
        try {
            $category = Category::create($data);

            $this->clearCategoryCache();

            return $category->load('products');
        } catch (\Exception $e) {
            Log::error('Failed to create category: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Update a category
     */
    public function updateCategory(Category $category, array $data): Category
    {
        try {
            $category->update((array_filter($data)));

            $this->clearCategoryCache($category->id);

            return $category->load('products');
        } catch (\Exception $e) {
            Log::error('Failed to update category: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Delete a category
     */
    public function deleteCategory(Category $category): bool
    {
        try {
            $categoryId = $category->id;
            $result = $category->delete();

            $this->clearCategoryCache($categoryId);

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to delete category: '.$e->getMessage());
            throw new \Exception('An error occurred on the server.');
        }
    }

    /**
     * Clear category cache
     */
    protected function clearCategoryCache(?int $categoryId = null): void
    {
        // Clear common per_page variations
        $commonPerPages = [10, 15, 20, 25, 30, 50, 100];
        foreach ($commonPerPages as $perPage) {
            Cache::forget("categories:list:per_page:{$perPage}");
        }

        // Clear specific category cache if provided
        if ($categoryId) {
            Cache::forget("category:{$categoryId}");
        }
    }
}
