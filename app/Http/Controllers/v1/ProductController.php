<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of products with pagination and filtering.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['category', 'price_min', 'price_max', 'in_stock', 'search']);
        $perPage = $request->input('per_page', 15);

        $products = $this->productService->getProducts($filters, $perPage);
        return self::paginated($products, ProductResource::class, 'Products retrieved successfully');
    }

    /**
     * Display the specified product.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function show(Product $product): JsonResponse
    {
        $product->load('categories');
        return self::success(new ProductResource($product), 'Product retrieved successfully');
    }

    /**
     * Store a newly created product.
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $product = $this->productService->createProduct($request->validated());

        return self::success(new ProductResource($product), 'Product created successfully', 201);
    }

    /**
     * Update the specified product.
     *
     * @param UpdateProductRequest $request
     * @param Product $product
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $product = $this->productService->updateProduct($product, $request->validated());

        return self::success(new ProductResource($product), 'Product updated successfully');
    }

    /**
     * Remove the specified product.
     *
     * @param Product $product
     * @return JsonResponse
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        $this->productService->deleteProduct($product);

        return self::success(null, 'Product deleted successfully');
    }

    /**
     * Display a listing of trashed products.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function trashed(Request $request): JsonResponse
    {
        $this->authorize('viewTrashed', Product::class);

        $perPage = $request->input('per_page', 15);
        $products = $this->productService->getTrashedProducts($perPage);

        return self::paginated($products, ProductResource::class, 'Trashed products retrieved successfully');
    }

    /**
     * Restore the specified trashed product.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function restore(string $slug): JsonResponse
    {
        $product = Product::onlyTrashed()->where('slug', $slug)->firstOrFail();

        $this->authorize('restore', $product);

        $this->productService->restoreProduct($product);
        $product->load('categories');

        return self::success(new ProductResource($product), 'Product restored successfully');
    }

    /**
     * Permanently delete the specified product.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function forceDelete(string $slug): JsonResponse
    {
        $product = Product::onlyTrashed()->where('slug', $slug)->firstOrFail();

        $this->authorize('forceDelete', $product);

        $this->productService->forceDeleteProduct($product);

        return self::success(null, 'Product permanently deleted successfully');
    }
}
