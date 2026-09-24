<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;


class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = in_array($request->query('status'), Order::STATUSES, true)
            ? $request->query('status')
            : null;

        $orders = Order::whereIn('store_id', $user->stores()->select('id'))
            ->with('store')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($request->filled('store'), fn ($q) => $q->where('store_id', (int) $request->query('store')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stores = $user->stores()->orderBy('name')->get(['id', 'name']);

        return view('dashboard.orders.index', [
            'orders' => $orders,
            'stores' => $stores,
            'status' => $status,
            'storeFilter' => $request->query('store'),
        ]);
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        $order->load(['items', 'store']);

        return view('dashboard.orders.show', compact('order'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('update', $order);

        // status is not mass-assignable, so it is set explicitly.
        $order->status = $request->validated('status');
        $order->save();

        return back()->with('success', 'Order status updated to '.$order->status.'.');
    }
}

app/Http/Controllers/Web/ProfileController.php

php
<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('dashboard.profile', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (is_string($request->input('email'))) {
            $request->merge(['email' => strtolower(trim($request->input('email')))]);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($data);

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        // Errors go into a named bag so they show only on the password form.
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed'],
        ]);

        // The "hashed" cast on User hashes this.
        $request->user()->update(['password' => $data['password']]);

        return back()->with('success', 'Password changed.');
    }
}
