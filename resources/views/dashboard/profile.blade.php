@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="mb-4">
        <h1 class="h4 mb-0">Profile</h1>
        <div class="text-muted">Your account details.</div>
    </div>

    <div class="row g-3" style="max-width: 900px;">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Account</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('dashboard.profile.update') }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                                   class="form-control @error('email') is-invalid @enderror" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Change password</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('dashboard.profile.password') }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current password</label>
                            <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                                   class="form-control @error('current_password', 'password') is-invalid @enderror" required>
                            @error('current_password', 'password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New password</label>
                            <input type="password" id="new_password" name="password" autocomplete="new-password"
                                   class="form-control @error('password', 'password') is-invalid @enderror" required>
                            @error('password', 'password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label">Confirm new password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password"
                                   class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Update password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection