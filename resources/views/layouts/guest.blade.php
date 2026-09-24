<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>@yield('title', 'Welcome') · {{ config('app.name') }}</title>
</head>
<body>
    <main class="min-vh-100 d-flex align-items-center justify-content-center p-3">
        <div style="width: 100%; max-width: 420px;">
            <div class="text-center mb-4">
                <i class="bi bi-whatsapp brand-mark fs-1"></i>
                <div class="fw-bold fs-4">{{ config('app.name') }}</div>
                <div class="text-muted small">Turn your catalog into WhatsApp orders</div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    @yield('content')
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>