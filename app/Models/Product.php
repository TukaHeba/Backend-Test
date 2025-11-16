<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'status',
    ];

    /**
     * The attributes that are not mass assignable
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'slug',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'float',
        ];
    }

    /**
     * Get the categories that belong to the product
     */
    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTimestamps();
    }

    /**
     * Get the order items for the product
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the cart items for the product
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the route key for the model
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Scope a query to only include active products
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include products in stock
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    /**
     * Scope a query to only include products out of stock
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('quantity', 0);
    }

    /**
     * Scope a query to only include products with low stock
     */
    public function scopeLowStock($query)
    {
        return $query->whereBetween('quantity', [1, 10]);
    }

    /**
     * Scope a query to only include available products
     */
    public function scopeAvailable($query)
    {
        return $query->active()->inStock();
    }

    /**
     * Scope a query to filter products by category
     */
    public function scopeCategory($query, $categorySlug)
    {
        return $query->whereHas('categories', function ($q) use ($categorySlug) {
            $q->where('slug', $categorySlug);
        });
    }

    /**
     * Scope a query to filter products by minimum price
     */
    public function scopePriceMin($query, $min)
    {
        return $query->where('price', '>=', $min);
    }

    /**
     * Scope a query to filter products by maximum price
     */
    public function scopePriceMax($query, $max)
    {
        return $query->where('price', '<=', $max);
    }

    /**
     * Scope a query to filter products by price range
     */
    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('price', [$min, $max]);
    }

    /**
     * Scope a query to search products by name
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    /**
     * Get the price formatted attribute
     */
    public function priceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->price, 2).' $',
        );
    }
}
