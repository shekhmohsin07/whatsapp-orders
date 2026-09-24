@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Dashboard</h1>
            <div class="text-muted">Hi {{ auth()->user()->name }}, here is what is happening today.</div>
        </div>
        <a href="{{ url('/dashboard/stores') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New store
        </a>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        @php
            $cards = [
                ['label' => 'Total Stores', 'value' => $stats['stores'], 'icon' => 'bi-shop', 'url' => '/dashboard/stores'],
                ['label' => 'Total Products', 'value' => $stats['products'], 'icon' => 'bi-box-seam', 'url' => '/dashboard/products'],
                ['label' => 'Total Orders', 'value' => $stats['orders'], 'icon' => 'bi-receipt', 'url' => '/dashboard/orders'],
                ['label' => 'Pending Orders', 'value' => $stats['pending'], 'icon' => 'bi-hourglass-split', 'url' => '/dashboard/orders?status=pending'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="col-6 col-lg-3">
                <a href="{{ url($card['url']) }}" class="stat-link">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon"><i class="bi {{ $card['icon'] }}"></i></div>
                            <div>
                                <div class="stat-value">{{ number_format($card['value']) }}</div>
                                <div class="text-muted small">{{ $card['label'] }}</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        {{-- Recent orders --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Recent orders</span>
                    <a href="{{ url('/dashboard/orders') }}" class="small">View all</a>
                </div>

                @if ($recentOrders->isEmpty())
                    <div class="card-body text-center text-muted py-5">
                        <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                        No orders yet. Share your store link to get your first one.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Customer</th>
                                    <th>Store</th>
                                    <th class="text-end">Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentOrders as $order)
                                    <tr>
                                        <td>
                                            <a href="{{ url('/dashboard/orders/'.$order->id) }}">#{{ $order->id }}</a>
                                        </td>
                                        <td>{{ $order->customer_name }}</td>
                                        <td class="text-muted">{{ $order->store->name }}</td>
                                        <td class="text-end">{{ $order->store->currency }}{{ number_format($order->total_amount, 2) }}</td>
                                        <td><x-status-badge :status="$order->status" /></td>
                                        <td class="text-muted small">{{ $order->created_at->format('M j, H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Store links --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Your public store links</div>

                @if ($stores->isEmpty())
                    <div class="card-body text-muted">You have not created a store yet.</div>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach ($stores as $store)
                            <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <div class="text-truncate">
                                    <div class="fw-semibold text-truncate">{{ $store->name }}</div>
                                    <div class="small text-muted text-truncate">/store/{{ $store->slug }}</div>
                                </div>
                                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                    <x-status-badge :status="$store->status" />
                                    <a href="{{ url('/store/'.$store->slug) }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-outline-primary" title="Open public page">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@endsection