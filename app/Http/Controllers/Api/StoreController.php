<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class StoreController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        // Scoped to the authenticated user, so nobody sees other users' stores.
        $stores = $request->user()
            ->stores()
            ->withCount(['products', 'orders'])
            ->latest()
            ->paginate(15);

        return $this->paginated($stores, StoreResource::class, 'Stores retrieved successfully');
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = Store::generateUniqueSlug($data['name']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        // user_id is set by the relationship, never by request input.
        $store = $request->user()->stores()->create($data);

        return $this->success(new StoreResource($store), 'Store created successfully', 201);
    }

    public function show(Store $store): JsonResponse
    {
        Gate::authorize('view', $store);

        $store->loadCount(['products', 'orders']);

        return $this->success(new StoreResource($store), 'Store retrieved successfully');
    }

    public function update(StoreRequest $request, Store $store): JsonResponse
    {
        Gate::authorize('update', $store);

        $data = $request->validated();

        // The slug is deliberately NOT regenerated on update, so public links keep working.
        if ($request->hasFile('logo')) {
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $store->update($data);

        return $this->success(new StoreResource($store), 'Store updated successfully');
    }

    public function destroy(Store $store): JsonResponse
    {
        Gate::authorize('delete', $store);

        // Collect files first: the DB cascade removes products but not their images.
        $files = $store->products()
            ->whereNotNull('image')
            ->pluck('image')
            ->push($store->logo)
            ->filter()
            ->all();

        $store->delete();

        Storage::disk('public')->delete($files);

        return $this->success(null, 'Store deleted successfully');
    }
}