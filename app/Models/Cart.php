<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * Get the user that owns the cart
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items for the cart
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Calculate total from cart items using their getSubtotal() method
     */
    public function getTotal(): float
    {
        return $this->loadMissing('cartItems')
            ->cartItems
            ->sum(fn ($item) => $item->getSubtotal());
    }
}
