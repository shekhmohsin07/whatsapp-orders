@extends('layouts.app')

@section('title', 'Order #'.$order->id)

@section('content')
    <div class="mb-4">
        <a href="{{ route('dashboard.orders.index') }}" class="text-decoration-none small">
            <i class="bi bi-arrow-left"></i> Back to orders
        </a>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
            <h1 class="h4 mb-0">Order #{{ $order->id }}</h1>
            <x-status-badge :status="$order->status" />
        </div>
        <div class="text-muted small">
            {{ $order->store->name }} · placed {{ $order->created_at->format('M j, Y \a\t H:i') }}
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white fw-semibold">Products</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ $order->store->currency }}{{ number_format($item->price, 2) }}</td>
                                    <td class="text-end">{{ $order->store->currency }}{{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th colspan="3" class="text-end">Total</th>
                                <th class="text-end">{{ $order->store->currency }}{{ number_format($order->total_amount, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header bg-white fw-semibold">Customer</div>
                <div class="card-body">
                    <div class="text-muted small">Name</div>
                    <div class="mb-2">{{ $order->customer_name }}</div>
                    <div class="text-muted small">Phone</div>
                    <div class="mb-2">{{ $order->customer_phone }}</div>
                    <div class="text-muted small">Address</div>
                    <div style="white-space: pre-line;">{{ $order->customer_address }}</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white fw-semibold">Order status</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('dashboard.orders.status', $order) }}">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="form-select mb-3 @error('status') is-invalid @enderror">
                            @foreach (\App\Models\Order::STATUSES as $s)
                                <option value="{{ $s }}" @selected($order->status === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        @error('status') <div class="invalid-feedback d-block mb-2">{{ $message }}</div> @enderror
                        <button type="submit" class="btn btn-primary w-100">Update status</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection