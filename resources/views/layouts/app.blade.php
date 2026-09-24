<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>

    <style>
        .sidebar { --bs-offcanvas-width: 260px; background: #fff !important; }
        .sidebar .nav-link {
            color: #4b5563; border-radius: .5rem; padding: .6rem .85rem;
            display: flex; align-items: center; gap: .65rem; font-weight: 500;
        }
        .sidebar .nav-link:hover { background: #f3f4f6; }
        .sidebar .nav-link.active { background: var(--brand-soft); color: var(--brand); }

        @media (min-width: 768px) {
            .sidebar {
                flex: 0 0 250px; width: 250px !important;
                position: sticky; top: 0; height: 100vh !important;
                border-right: 1px solid #eceef1 !important;
            }
        }

        .stat-card .stat-icon {
            width: 46px; height: 46px; border-radius: .75rem;
            background: var(--brand-soft); color: var(--brand);
            display: flex; align-items: center; justify-content: center; font-size: 1.35rem;
        }
        .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; line-height: 1.1; }
        a.stat-link { text-decoration: none; color: inherit; }
        a.stat-link:hover .card { border-color: var(--brand); }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Mobile top bar --}}
    <div class="d-md-none bg-white border-bottom px-3 py-2 d-flex align-items-center gap-2">
        <button class="btn btn-outline-secondary btn-sm" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Open menu">
            <i class="bi bi-list fs-5"></i>
        </button>
        <i class="bi bi-whatsapp brand-mark"></i>
        <span class="fw-bold">{{ config('app.name') }}</span>
    </div>

    <div class="d-md-flex min-vh-100">
        <aside id="sidebar" class="sidebar offcanvas-md offcanvas-start" tabindex="-1">
            <div class="offcanvas-header border-bottom">
                <span class="fw-bold">{{ config('app.name') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Close"></button>
            </div>

            <div class="offcanvas-body d-flex flex-column p-3 w-100">
                <div class="d-none d-md-flex align-items-center gap-2 mb-4 px-2">
                    <i class="bi bi-whatsapp brand-mark fs-4"></i>
                    <span class="fw-bold fs-5">{{ config('app.name') }}</span>
                </div>

                <nav class="nav flex-column gap-1">
                    <a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="{{ url('/dashboard') }}">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                    <a class="nav-link {{ request()->is('dashboard/stores*') ? 'active' : '' }}" href="{{ url('/dashboard/stores') }}">
                        <i class="bi bi-shop"></i> Stores
                    </a>
                    <a class="nav-link {{ request()->is('dashboard/products*') ? 'active' : '' }}" href="{{ url('/dashboard/products') }}">
                        <i class="bi bi-box-seam"></i> Products
                    </a>
                    <a class="nav-link {{ request()->is('dashboard/orders*') ? 'active' : '' }}" href="{{ url('/dashboard/orders') }}">
                        <i class="bi bi-receipt"></i> Orders
                    </a>
                    <a class="nav-link {{ request()->is('dashboard/profile*') ? 'active' : '' }}" href="{{ url('/dashboard/profile') }}">
                        <i class="bi bi-person-circle"></i> Profile
                    </a>
                </nav>

                <div class="mt-auto pt-3 border-top">
                    <div class="px-2 mb-2">
                        <div class="fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                        <div class="text-muted small text-truncate">{{ auth()->user()->email }}</div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                            <i class="bi bi-box-arrow-right"></i> Log out
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="flex-grow-1 p-3 p-md-4" style="min-width: 0;">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>