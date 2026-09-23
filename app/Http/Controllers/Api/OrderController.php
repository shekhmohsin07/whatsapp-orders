<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Store;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request, Store $store): JsonResponse
    {
        Gate::authorize('view', $store);

        // Optional filter: GET /api/stores/{store}/orders?status=pending
        $request->validate([
            'status' => ['nullable', Rule::in(Order::STATUSES)],
        ]);

        $orders = $store->orders()
            ->withCount('items')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->latest()
            ->paginate(15);

        return $this->paginated($orders, OrderResource::class, 'Orders retrieved successfully');
    }

    public function show(Order $order): JsonResponse
    {
        Gate::authorize('view', $order);

        $order->load(['items', 'store']);

        return $this->success(new OrderResource($order), 'Order retrieved successfully');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        Gate::authorize('update', $order);

        // status is not mass-assignable, so it is set explicitly here.
        $order->status = $request->validated('status');
        $order->save();

        $order->load('items');

        return $this->success(new OrderResource($order), 'Order status updated successfully');
    }
}