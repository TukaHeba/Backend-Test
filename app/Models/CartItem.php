<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
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
     * Get the cart that owns the cart item
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Get the product that owns the cart item
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate the subtotal
     */
    public function getSubtotal(): float
    {
        return $this->quantity * $this->price;
    }
}
