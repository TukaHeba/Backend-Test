<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of the user's orders.
     * Admins can see all orders.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);
        $perPage = $request->input('per_page', 15);

        $user = Auth::user();
        $userId = $user->role === 'admin' ? null : $user->id;

        $orders = $this->orderService->getOrders($userId, $perPage);

        return self::paginated($orders, OrderResource::class, 'Orders retrieved successfully');
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order = $this->orderService->getOrder($order);

        return self::success(new OrderResource($order), 'Order retrieved successfully');
    }

    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);

        $order = $this->orderService->createOrder(Auth::id(), $request->validated()['items']);

        return self::success(new OrderResource($order), 'Order created successfully', 201);
    }

    /**
     * Cancel the specified order.
     */
    public function cancel(Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);

        $order = $this->orderService->cancelOrder($order);

        return self::success(new OrderResource($order), 'Order cancelled successfully');
    }
}
