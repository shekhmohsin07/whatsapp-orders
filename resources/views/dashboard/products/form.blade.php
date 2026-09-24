@extends('layouts.app')

@section('title', $product->exists ? 'Edit product' : 'Add product')

@section('content')
    <div class="mb-4">
        <a href="{{ route('dashboard.products.index') }}" class="text-decoration-none small">
            <i class="bi bi-arrow-left"></i> Back to products
        </a>
        <h1 class="h4 mt-1 mb-0">{{ $product->exists ? 'Edit product' : 'Add product' }}</h1>
    </div>

    <div class="card" style="max-width: 720px;">
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data" novalidate
                  action="{{ $product->exists ? route('dashboard.products.update', $product) : route('dashboard.products.store') }}">
                @csrf
                @if ($product->exists)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    @if ($product->exists)
                        <label class="form-label">Store</label>
                        <input type="text" class="form-control" value="{{ $product->store->name }}" disabled>
                    @else
                        <label for="store_id" class="form-label">Store</label>
                        <select id="store_id" name="store_id" class="form-select @error('store_id') is-invalid @enderror" required>
                            @foreach ($stores as $s)
                                <option value="{{ $s->id }}" @selected((string) old('store_id', $selectedStoreId) === (string) $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('store_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @endif
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Product name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" id="price" name="price" step="0.01" min="0"
                               value="{{ old('price', $product->exists ? $product->price : '') }}"
                               class="form-control @error('price') is-invalid @enderror" required>
                        @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                            <option value="active" @selected(old('status', $product->status) === 'active')>Active (visible to customers)</option>
                            <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Inactive (hidden)</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="image" class="form-label">Image</label>
                    @if ($product->image_url)
                        <div class="mb-2">
                            <img src="{{ $product->image_url }}" alt="Current image" height="90" class="rounded border">
                        </div>
                    @endif
                    <input type="file" id="image" name="image" accept="image/png,image/jpeg,image/webp"
                           class="form-control @error('image') is-invalid @enderror">
                    @error('image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">JPG, PNG or WebP, up to 2 MB.{{ $product->exists ? ' Leave empty to keep the current image.' : '' }}</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ $product->exists ? 'Save changes' : 'Add product' }}</button>
                    <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection