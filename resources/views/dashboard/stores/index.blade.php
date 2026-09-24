@extends('layouts.app')

@section('title', 'Stores')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Stores</h1>
            <div class="text-muted">Each store has its own public link and WhatsApp number.</div>
        </div>
        <a href="{{ route('dashboard.stores.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> New store
        </a>
    </div>

    <div class="card">
        @if ($stores->isEmpty())
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-shop fs-1 d-block mb-2"></i>
                You have no stores yet.
                <div class="mt-3">
                    <a href="{{ route('dashboard.stores.create') }}" class="btn btn-primary">Create your first store</a>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Store</th>
                            <th>WhatsApp</th>
                            <th class="text-center">Products</th>
                            <th class="text-center">Orders</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stores as $store)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($store->logo_url)
                                            <img src="{{ $store->logo_url }}" alt="" width="40" height="40"
                                                 class="rounded object-fit-cover flex-shrink-0">
                                        @else
                                            <div class="rounded bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                                 style="width:40px;height:40px;">
                                                <i class="bi bi-shop text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $store->name }}</div>
                                            <div class="small text-muted">/store/{{ $store->slug }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-nowrap">{{ $store->whatsapp_number }}</td>
                                <td class="text-center">
                                    <a href="{{ route('dashboard.products.index', ['store' => $store->id]) }}">{{ $store->products_count }}</a>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('dashboard.orders.index', ['store' => $store->id]) }}">{{ $store->orders_count }}</a>
                                </td>
                                <td><x-status-badge :status="$store->status" /></td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('public.store', $store->slug) }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-outline-secondary" title="Open public page">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <a href="{{ route('dashboard.stores.edit', $store) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST" action="{{ route('dashboard.stores.toggle', $store) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            {{ $store->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('dashboard.stores.destroy', $store) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this store together with all its products and orders? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($stores->hasPages())
                <div class="card-footer bg-white">{{ $stores->links() }}</div>
            @endif
        @endif
    </div>
@endsection