@extends('layouts.app')

@section('title', $store->exists ? 'Edit store' : 'New store')

@section('content')
    <div class="mb-4">
        <a href="{{ route('dashboard.stores.index') }}" class="text-decoration-none small">
            <i class="bi bi-arrow-left"></i> Back to stores
        </a>
        <h1 class="h4 mt-1 mb-0">{{ $store->exists ? 'Edit store' : 'New store' }}</h1>
    </div>

    <div class="card" style="max-width: 720px;">
        <div class="card-body p-4">
            <form method="POST" enctype="multipart/form-data" novalidate
                  action="{{ $store->exists ? route('dashboard.stores.update', $store) : route('dashboard.stores.store') }}">
                @csrf
                @if ($store->exists)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label for="name" class="form-label">Store name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $store->name) }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if (! $store->exists)
                        <div class="form-text">Your public link is created from the name, for example /store/abc-restaurant.</div>
                    @else
                        <div class="form-text">Public link: /store/{{ $store->slug }} (it does not change when you rename the store).</div>
                    @endif
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea id="description" name="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror">{{ old('description', $store->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-7">
                        <label for="whatsapp_number" class="form-label">WhatsApp number</label>
                        <input type="text" id="whatsapp_number" name="whatsapp_number" inputmode="tel"
                               value="{{ old('whatsapp_number', $store->whatsapp_number) }}"
                               placeholder="+15551234567"
                               class="form-control @error('whatsapp_number') is-invalid @enderror" required>
                        @error('whatsapp_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">Include the country code. Orders are sent to this number.</div>
                    </div>
                    <div class="col-md-5">
                        <label for="currency" class="form-label">Currency</label>
                        <input type="text" id="currency" name="currency" maxlength="8"
                               value="{{ old('currency', $store->currency) }}"
                               placeholder="$ or USD"
                               class="form-control @error('currency') is-invalid @enderror" required>
                        @error('currency') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text">A symbol ($, €) or a code (USD).</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" @selected(old('status', $store->status) === 'active')>Active (visible to customers)</option>
                        <option value="inactive" @selected(old('status', $store->status) === 'inactive')>Inactive (hidden)</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-4">
                    <label for="logo" class="form-label">Logo</label>
                    @if ($store->logo_url)
                        <div class="mb-2">
                            <img src="{{ $store->logo_url }}" alt="Current logo" height="64" class="rounded border">
                        </div>
                    @endif
                    <input type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp"
                           class="form-control @error('logo') is-invalid @enderror">
                    @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">JPG, PNG or WebP, up to 2 MB.{{ $store->exists ? ' Leave empty to keep the current logo.' : '' }}</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">{{ $store->exists ? 'Save changes' : 'Create store' }}</button>
                    <a href="{{ route('dashboard.stores.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection