@extends('layouts.app')

@section('title', 'Products')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h4 mb-0">Products</h1>
            <div class="text-muted">Only active products appear on your public store.</div>
        </div>
        <a href="{{ route('dashboard.products.create', array_filter(['store' => $storeFilter])) }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add product
        </a>
    </div>

    @if ($stores->count() > 1)
        <form method="GET" class="mb-3">
            <select name="store" class="form-select" style="max-width: 280px;" onchange="this.form.submit()">
                <option value="">All stores</option>
                @foreach ($stores as $s)
                    <option value="{{ $s->id }}" @selected((string) $storeFilter === (string) $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </form>
    @endif

    <div class="card">
        @if ($products->isEmpty())
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                No products yet.
                <div class="mt-3">
                    <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary">Add your first product</a>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Product</th>
                            <th>Store</th>
                            <th class="text-end">Price</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="" width="48" height="48"
                                                 class="rounded object-fit-cover flex-shrink-0">
                                        @else
                                            <div class="rounded bg-light d-flex align-items-center justify-content-center flex-shrink-0"
                                                 style="width:48px;height:48px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="fw-semibold">{{ $product->name }}</div>
                                            @if ($product->description)
                                                <div class="small text-muted text-truncate" style="max-width: 320px;">{{ $product->description }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="text-muted">{{ $product->store->name }}</td>
                                <td class="text-end text-nowrap">{{ $product->store->currency }}{{ number_format($product->price, 2) }}</td>
                                <td><x-status-badge :status="$product->status" /></td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('dashboard.products.edit', $product) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form method="POST" action="{{ route('dashboard.products.toggle', $product) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                                            {{ $product->status === 'active' ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('dashboard.products.destroy', $product) }}" class="d-inline"
                                          onsubmit="return confirm('Delete this product? Past orders keep their details.');">
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

            @if ($products->hasPages())
                <div class="card-footer bg-white">{{ $products->links() }}</div>
            @endif
        @endif
    </div>
@endsection