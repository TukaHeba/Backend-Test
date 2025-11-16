<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine if the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        return true; 
    }

    /**
     * Determine if the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->role === 'admin' || $user->id === $order->user_id;
    }

    /**
     * Determine if the user can create orders.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        return $user->role === 'admin' || $user->id === $order->user_id;
    }
}

