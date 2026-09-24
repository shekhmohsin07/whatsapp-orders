<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $products = Product::whereIn('store_id', $user->stores()->select('id'))
            ->with('store')
            ->when($request->filled('store'), fn ($q) => $q->where('store_id', (int) $request->query('store')))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $stores = $user->stores()->orderBy('name')->get(['id', 'name']);

        return view('dashboard.products.index', [
            'products' => $products,
            'stores' => $stores,
            'storeFilter' => $request->query('store'),
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $stores = $request->user()->stores()->orderBy('name')->get(['id', 'name']);

        if ($stores->isEmpty()) {
            return redirect()
                ->route('dashboard.stores.create')
                ->with('error', 'Create a store first, then add products to it.');
        }

        return view('dashboard.products.form', [
            'product' => new Product(),
            'stores' => $stores,
            'selectedStoreId' => $request->query('store', $stores->first()->id),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $storeId = $request->validate([
            'store_id' => [
                'required',
                Rule::exists('stores', 'id')->where('user_id', $request->user()->id),
            ],
        ])['store_id'];

        $store = Store::findOrFail($storeId);
        Gate::authorize('update', $store);

        $data = $request->validated();
        $data['slug'] = Product::generateUniqueSlug($store->id, $data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $store->products()->create($data);

        return redirect()
            ->route('dashboard.products.index', ['store' => $store->id])
            ->with('success', 'Product added.');
    }

    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);

        $product->load('store');

        return view('dashboard.products.form', [
            'product' => $product,
            'stores' => collect(),
            'selectedStoreId' => $product->store_id,
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
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

        return redirect()
            ->route('dashboard.products.index', ['store' => $product->store_id])
            ->with('success', 'Product updated.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);

        $product->update([
            'status' => $product->status === Product::STATUS_ACTIVE
                ? Product::STATUS_INACTIVE
                : Product::STATUS_ACTIVE,
        ]);

        return back()->with('success', "Product is now {$product->status}.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $image = $product->image;

        $product->delete();

        if ($image) {
            Storage::disk('public')->delete($image);
        }

        return back()->with('success', 'Product deleted.');
    }
}
