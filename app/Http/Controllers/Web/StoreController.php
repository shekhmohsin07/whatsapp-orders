<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class StoreController extends Controller
{
     public function index(Request $request): View
    {
        $stores = $request->user()
            ->stores()
            ->withCount(['products', 'orders'])
            ->latest()
            ->paginate(10);

        return view('dashboard.stores.index', compact('stores'));
    }

    public function create(): View
    {
        return view('dashboard.stores.form', ['store' => new Store()]);
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['slug'] = Store::generateUniqueSlug($data['name']);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $store = $request->user()->stores()->create($data);

        return redirect()
            ->route('dashboard.stores.index')
            ->with('success', "Store created. Your link: /store/{$store->slug}");
    }

    public function edit(Store $store): View
    {
        Gate::authorize('update', $store);

        return view('dashboard.stores.form', compact('store'));
    }

    public function update(StoreRequest $request, Store $store): RedirectResponse
    {
        Gate::authorize('update', $store);

        $data = $request->validated();

        // The slug is not regenerated, so shared links keep working.
        if ($request->hasFile('logo')) {
            if ($store->logo) {
                Storage::disk('public')->delete($store->logo);
            }
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $store->update($data);

        return redirect()->route('dashboard.stores.index')->with('success', 'Store updated.');
    }

    public function toggle(Store $store): RedirectResponse
    {
        Gate::authorize('update', $store);

        $store->update([
            'status' => $store->status === Store::STATUS_ACTIVE
                ? Store::STATUS_INACTIVE
                : Store::STATUS_ACTIVE,
        ]);

        return back()->with('success', "Store is now {$store->status}.");
    }

    public function destroy(Store $store): RedirectResponse
    {
        Gate::authorize('delete', $store);

        // The DB cascade removes products and orders, but not their files.
        $files = $store->products()
            ->whereNotNull('image')
            ->pluck('image')
            ->push($store->logo)
            ->filter()
            ->all();

        $store->delete();

        Storage::disk('public')->delete($files);

        return redirect()->route('dashboard.stores.index')->with('success', 'Store deleted.');
    }
}
