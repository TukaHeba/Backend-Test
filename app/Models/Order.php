<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'total',
        'status',
    ];

    /**
     * The attributes that are not mass assignable
     *
     * @var array<int, string>
     */
    protected $guarded = [
        'order_number'
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'float',
        ];
    }

    /**
     * Get the user that owns the order
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items for the order
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

}
