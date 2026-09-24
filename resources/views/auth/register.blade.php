@extends('layouts.guest')

@section('title', 'Create account')

@section('content')
    <h1 class="h4 mb-1">Create your account</h1>
    <p class="text-muted mb-4">Set up your catalog and start taking orders on WhatsApp.</p>

    <form method="POST" action="{{ route('register.store') }}" novalidate>
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}"
                   class="form-control @error('name') is-invalid @enderror"
                   autocomplete="name" autofocus required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="form-control @error('email') is-invalid @enderror"
                   autocomplete="email" required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input type="password" id="password" name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   autocomplete="new-password" required>
            <div class="form-text">At least 8 characters.</div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100">Create account</button>
    </form>

    <p class="text-center text-muted small mt-4 mb-0">
        Already registered? <a href="{{ route('login') }}">Sign in</a>
    </p>
@endsection