<?php

namespace App\Models;

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
        'slug'
    ];

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
}
