<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Store;
use Illuminate\View\View;

class PublicStoreController extends Controller
{
    public function show(string $slug): View
    {
        $store = Store::active()->where('slug', $slug)->firstOrFail();

        $products = $store->products()->active()->orderBy('name')->get();

        return view('public.store', compact('store', 'products'));
    }
}
