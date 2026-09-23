<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    use ApiResponse;

    public function index(Store $store): JsonResponse
    {
        Gate::authorize('view', $store);

        $products = $store->products()->latest()->paginate(15);

        return $this->paginated($products, ProductResource::class, 'Products retrieved successfully');
    }

    public function store(ProductRequest $request, Store $store): JsonResponse
    {
        // You may only add products to a store you own.
        Gate::authorize('update', $store);

        $data = $request->validated();
        $data['slug'] = Product::generateUniqueSlug($store->id, $data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // store_id is set by the relationship, never by request input.
        $product = $store->products()->create($data);

        return $this->success(new ProductResource($product), 'Product created successfully', 201);
    }

    public function show(Product $product): JsonResponse
    {
        Gate::authorize('view', $product);

        return $this->success(new ProductResource($product), 'Product retrieved successfully');
    }

    public function update(ProductRequest $request, Product $product): JsonResponse
    {
        Gate::authorize('update', $product);

        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($data);

        return $this->success(new ProductResource($product), 'Product updated successfully');
    }

    public function destroy(Product $product): JsonResponse
    {
        Gate::authorize('delete', $product);

        $image = $product->image;

        $product->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return $this->success(null, 'Product deleted successfully');
    }
}