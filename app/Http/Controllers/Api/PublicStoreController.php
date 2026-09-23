<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Services\OrderService;
use App\Services\WhatsAppService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class PublicStoreController extends Controller
{
    use ApiResponse;

    public function show(string $slug): JsonResponse
    {
        $store = $this->findActiveStore($slug);

        // Only active products are ever exposed publicly.
        $store->load(['products' => fn ($query) => $query->active()->orderBy('name')]);

        return $this->success(new StoreResource($store), 'Store retrieved successfully');
    }

    public function products(string $slug): JsonResponse
    {
        $store = $this->findActiveStore($slug);

        $products = $store->products()->active()->orderBy('name')->get();

        return $this->success(ProductResource::collection($products), 'Products retrieved successfully');
    }

    public function storeOrder(
        CreateOrderRequest $request,
        string $slug,
        OrderService $orderService,
        WhatsAppService $whatsApp
    ): JsonResponse {
        $store = $this->findActiveStore($slug);

        // Prices, ownership and active-status checks all happen inside the service.
        $order = $orderService->createOrder($store, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'order' => (new OrderResource($order))->resolve(),
            'whatsapp_url' => $whatsApp->generateWhatsAppUrl($store, $order),
        ], 201);
    }

    /**
     * Inactive or unknown stores return 404, as if they did not exist.
     */
    private function findActiveStore(string $slug): Store
    {
        return Store::active()->where('slug', $slug)->firstOrFail();
    }
}