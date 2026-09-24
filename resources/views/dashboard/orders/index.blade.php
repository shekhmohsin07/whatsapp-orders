@extends('layouts.app')

@section('title', 'Orders')

@section('content')
    <div class="mb-4">
        <h1 class="h4 mb-0">Orders</h1>
        <div class="text-muted">Orders placed through your public stores.</div>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        {{-- Status filter --}}
        <ul class="nav nav-pills gap-1">
            @foreach ([null => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $value => $label)
                <li class="nav-item">
                    <a class="nav-link py-1 px-3 {{ $status === $value ? 'active' : 'text-secondary bg-white border' }}"
                       @if ($status === $value) style="background: var(--brand);" @endif
                       href="{{ route('dashboard.orders.index', array_filter(['status' => $value, 'store' => $storeFilter])) }}">
                        {{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>

        {{-- Store filter --}}
        @if ($stores->count() > 1)
            <form method="GET">
                @if ($status) <input type="hidden" name="status" value="{{ $status }}"> @endif
                <select name="store" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All stores</option>
                    @foreach ($stores as $s)
                        <option value="{{ $s->id }}" @selected((string) $storeFilter === (string) $s->id)>{{ $s->name }}</option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    <div class="card">
        @if ($orders->isEmpty())
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                No orders found.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td class="fw-semibold">#{{ $order->id }}</td>
                                <td>
                                    {{ $order->customer_name }}
                                    <div class="small text-muted">{{ $order->store->name }}</div>
                                </td>
                                <td class="text-nowrap">{{ $order->customer_phone }}</td>
                                <td class="text-end text-nowrap">{{ $order->store->currency }}{{ number_format($order->total_amount, 2) }}</td>
                                <td><x-status-badge :status="$order->status" /></td>
                                <td class="text-muted small text-nowrap">{{ $order->created_at->format('M j, Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('dashboard.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($orders->hasPages())
                <div class="card-footer bg-white">{{ $orders->links() }}</div>
            @endif
        @endif
    </div>
@endsection