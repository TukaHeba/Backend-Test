<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use App\Observers\CategoryObserver;
use App\Policies\ProductPolicy;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);
        Order::observe(OrderObserver::class);

        // Register policies
        Gate::policy(Product::class, ProductPolicy::class);
    }
}
