<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // Subquery of the ids of stores this user owns.
        $storeIds = $user->stores()->select('id');

        $stats = [
            'stores' => $user->stores()->count(),
            'products' => Product::whereIn('store_id', $storeIds)->count(),
            'orders' => Order::whereIn('store_id', $storeIds)->count(),
            'pending' => Order::whereIn('store_id', $storeIds)
                ->where('status', Order::STATUS_PENDING)
                ->count(),
        ];

        $recentOrders = Order::whereIn('store_id', $storeIds)
            ->with('store')
            ->latest()
            ->take(5)
            ->get();

        $stores = $user->stores()->latest()->take(5)->get();

        return view('dashboard.index', compact('stats', 'recentOrders', 'stores'));
    }
}
