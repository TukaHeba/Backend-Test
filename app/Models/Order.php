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

    /**
     * Calculate total from order items using their getSubtotal() method
     *
     * @return float
     */
    public function calculateTotalFromItems(): float
    {
        return $this->loadMissing('orderItems')
            ->orderItems
            ->sum(fn($item) => $item->getSubtotal());
    }

    /**
     * Scope a query to only include cancelled orders
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to only include orders older than the given date
     */
    public function scopeOlderThan($query, $date)
    {
        return $query->where('updated_at', '<', $date);
    }

    /**
     * Check if the order is cancelled
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
}
