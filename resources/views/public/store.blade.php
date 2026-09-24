<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>{{ $store->name }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($store->description, 150) }}">

    <style>
        body { padding-bottom: 80px; }

        .store-header { background: #fff; border-bottom: 1px solid #eceef1; }
        .store-logo { width: 72px; height: 72px; object-fit: cover; border-radius: 1rem; }
        .store-logo-fallback {
            width: 72px; height: 72px; border-radius: 1rem; background: var(--brand-soft);
            color: var(--brand); display: flex; align-items: center; justify-content: center; font-size: 2rem;
        }

        .product-card { overflow: hidden; height: 100%; }
        .product-img { width: 100%; aspect-ratio: 4 / 3; object-fit: cover; background: #f1f3f5; display: block; }
        .product-img-fallback {
            width: 100%; aspect-ratio: 4 / 3; background: #f1f3f5; color: #adb5bd;
            display: flex; align-items: center; justify-content: center; font-size: 2rem;
        }
        .product-name { font-weight: 600; line-height: 1.25; }
        .product-price { color: var(--brand); font-weight: 700; }

        .qty-stepper { display: flex; align-items: center; }
        .qty-stepper .btn { width: 36px; padding: .25rem 0; }
        .qty-stepper input { width: 100%; min-width: 0; text-align: center; border-left: 0; border-right: 0; border-radius: 0; }

        .btn-whatsapp { background: #25D366; border-color: #25D366; color: #fff; font-weight: 600; }
        .btn-whatsapp:hover, .btn-whatsapp:focus { background: #1ebe5b; border-color: #1ebe5b; color: #fff; }
        .btn-whatsapp:disabled { background: #25D366; border-color: #25D366; color: #fff; opacity: .65; }

        .cart-bar {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 1030;
            background: #fff; border-top: 1px solid #dee2e6;
            padding: .65rem 1rem calc(.65rem + env(safe-area-inset-bottom, 0px));
            box-shadow: 0 -4px 16px rgba(0, 0, 0, .06);
        }

        html { scroll-behavior: smooth; }
    </style>
</head>
<body>
    {{-- Header --}}
    <header class="store-header py-4">
        <div class="container" style="max-width: 1100px;">
            <div class="d-flex align-items-center gap-3">
                @if ($store->logo_url)
                    <img src="{{ $store->logo_url }}" alt="{{ $store->name }} logo" class="store-logo">
                @else
                    <div class="store-logo-fallback"><i class="bi bi-shop"></i></div>
                @endif
                <div>
                    <h1 class="h3 mb-1">{{ $store->name }}</h1>
                    @if ($store->description)
                        <p class="text-muted mb-0">{{ $store->description }}</p>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <main class="container py-4" style="max-width: 1100px;">
        {{-- Products --}}
        @if ($products->isEmpty())
            <div class="card">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                    This store has no products available right now.
                </div>
            </div>
        @else
            <div class="row g-3">
                @foreach ($products as $product)
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card product-card" data-product-id="{{ $product->id }}">
                            @if ($product->image_url)
                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="product-img" loading="lazy">
                            @else
                                <div class="product-img-fallback"><i class="bi bi-image"></i></div>
                            @endif

                            <div class="card-body d-flex flex-column p-2 p-sm-3">
                                <div class="product-name">{{ $product->name }}</div>
                                @if ($product->description)
                                    <div class="small text-muted mb-1">{{ \Illuminate\Support\Str::limit($product->description, 70) }}</div>
                                @endif
                                <div class="product-price mb-2">{{ $store->currency }}{{ rtrim(rtrim(number_format($product->price, 2, '.', ','), '0'), '.') }}</div>

                                <div class="mt-auto">
                                    <div class="qty-stepper mb-2">
                                        <button type="button" class="btn btn-outline-secondary" data-qty="dec" aria-label="Decrease quantity">−</button>
                                        <input type="text" inputmode="numeric" class="form-control" value="1" readonly aria-label="Quantity" data-qty-input>
                                        <button type="button" class="btn btn-outline-secondary" data-qty="inc" aria-label="Increase quantity">+</button>
                                    </div>
                                    <button type="button" class="btn btn-primary btn-sm w-100" data-add-to-cart>
                                        Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Cart --}}
        <section id="cart" class="card mt-4" style="scroll-margin-top: 1rem;">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-cart3"></i> Cart</div>
            <div class="card-body">
                <div id="cart-empty" class="text-muted">Your cart is empty. Add something from the menu above.</div>

                <ul id="cart-list" class="list-group list-group-flush mb-3 d-none"></ul>

                <div id="cart-summary" class="d-none">
                    <div class="d-flex justify-content-between fs-5 fw-bold mb-3">
                        <span>Total</span>
                        <span id="cart-total">0</span>
                    </div>
                    <button type="button" id="checkout-btn" class="btn btn-primary w-100">Checkout</button>
                </div>

                {{-- Checkout form --}}
                <form id="checkout-form" class="d-none mt-4" novalidate>
                    <h2 class="h6 mb-3">Your details</h2>

                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Name</label>
                        <input type="text" id="customer_name" name="customer_name" class="form-control"
                               autocomplete="name" maxlength="255" required>
                    </div>
                    <div class="mb-3">
                        <label for="customer_phone" class="form-label">Phone</label>
                        <input type="tel" id="customer_phone" name="customer_phone" class="form-control"
                               autocomplete="tel" inputmode="tel" placeholder="+123456789" maxlength="20" required>
                    </div>
                    <div class="mb-3">
                        <label for="customer_address" class="form-label">Address</label>
                        <textarea id="customer_address" name="customer_address" class="form-control" rows="2"
                                  autocomplete="street-address" maxlength="500" required></textarea>
                    </div>

                    <div id="form-alert" class="alert alert-danger d-none" role="alert"></div>

                    <button type="submit" id="submit-btn" class="btn btn-whatsapp btn-lg w-100">
                        <i class="bi bi-whatsapp"></i> <span>Order on WhatsApp</span>
                    </button>
                </form>

                <div id="success-alert" class="alert alert-success d-none mt-3 mb-0" role="alert"></div>
            </div>
        </section>
    </main>

    {{-- Sticky cart bar --}}
    <div id="cart-bar" class="cart-bar d-none">
        <div class="container d-flex justify-content-between align-items-center gap-3" style="max-width: 1100px;">
            <div>
                <div class="small text-muted" id="cart-bar-count">0 items</div>
                <div class="fw-bold" id="cart-bar-total">0</div>
            </div>
            <a href="#cart" class="btn btn-primary">View cart</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            'use strict';

            const CURRENCY = @json($store->currency);
            const ORDER_URL = @json(url('/api/public/stores/'.$store->slug.'/orders'));
            const MAX_QTY = 99;

            // Display data only. The server never trusts these prices.
            const PRODUCTS = {};
            @json($products->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price_cents' => (int) round($p->price * 100),
            ])->values()).forEach(function (p) { PRODUCTS[p.id] = p; });

            // productId -> quantity
            const cart = new Map();
            let submitting = false;

            const $ = (id) => document.getElementById(id);
            const cartList = $('cart-list');
            const cartEmpty = $('cart-empty');
            const cartSummary = $('cart-summary');
            const cartTotal = $('cart-total');
            const cartBar = $('cart-bar');
            const form = $('checkout-form');
            const formAlert = $('form-alert');
            const successAlert = $('success-alert');
            const submitBtn = $('submit-btn');

            function esc(value) {
                const div = document.createElement('div');
                div.textContent = String(value);
                return div.innerHTML;
            }

            function money(cents) {
                let n = (cents / 100).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                if (n.endsWith('.00')) n = n.slice(0, -3);
                return /^[A-Za-z]+$/.test(CURRENCY) ? CURRENCY + ' ' + n : CURRENCY + n;
            }

            function render() {
                let totalCents = 0;
                let count = 0;
                let html = '';

                cart.forEach(function (qty, id) {
                    const p = PRODUCTS[id];
                    if (!p) return;
                    const line = p.price_cents * qty;
                    totalCents += line;
                    count += qty;

                    html += '<li class="list-group-item px-0 d-flex justify-content-between align-items-center gap-2">' +
                        '<div class="me-auto">' +
                            '<div class="fw-semibold">' + esc(p.name) + ' x' + qty + '</div>' +
                            '<div class="small text-muted">' + esc(money(p.price_cents)) + ' each</div>' +
                        '</div>' +
                        '<div class="fw-semibold text-nowrap">' + esc(money(line)) + '</div>' +
                        '<div class="btn-group btn-group-sm">' +
                            '<button type="button" class="btn btn-outline-secondary" data-cart="dec" data-id="' + id + '" aria-label="Decrease">−</button>' +
                            '<button type="button" class="btn btn-outline-secondary" data-cart="inc" data-id="' + id + '" aria-label="Increase">+</button>' +
                            '<button type="button" class="btn btn-outline-danger" data-cart="remove" data-id="' + id + '" aria-label="Remove"><i class="bi bi-x-lg"></i></button>' +
                        '</div>' +
                    '</li>';
                });

                cartList.innerHTML = html;

                const hasItems = cart.size > 0;
                cartEmpty.classList.toggle('d-none', hasItems);
                cartList.classList.toggle('d-none', !hasItems);
                cartSummary.classList.toggle('d-none', !hasItems);
                cartBar.classList.toggle('d-none', !hasItems);

                if (!hasItems) form.classList.add('d-none');

                cartTotal.textContent = money(totalCents);
                $('cart-bar-total').textContent = money(totalCents);
                $('cart-bar-count').textContent = count + (count === 1 ? ' item' : ' items');
            }

            // Product card: quantity stepper + Add to Cart
            document.addEventListener('click', function (e) {
                const stepBtn = e.target.closest('[data-qty]');
                if (stepBtn) {
                    const input = stepBtn.closest('.qty-stepper').querySelector('[data-qty-input]');
                    let value = parseInt(input.value, 10) || 1;
                    value += stepBtn.dataset.qty === 'inc' ? 1 : -1;
                    input.value = Math.min(MAX_QTY, Math.max(1, value));
                    return;
                }

                const addBtn = e.target.closest('[data-add-to-cart]');
                if (addBtn) {
                    const card = addBtn.closest('[data-product-id]');
                    const id = Number(card.dataset.productId);
                    const input = card.querySelector('[data-qty-input]');
                    const qty = parseInt(input.value, 10) || 1;

                    // Adding the same product again merges into one line.
                    cart.set(id, Math.min(MAX_QTY, (cart.get(id) || 0) + qty));
                    input.value = 1;
                    successAlert.classList.add('d-none');
                    render();

                    const original = addBtn.textContent;
                    addBtn.textContent = 'Added ✓';
                    setTimeout(function () { addBtn.textContent = original; }, 900);
                    return;
                }

                const cartBtn = e.target.closest('[data-cart]');
                if (cartBtn) {
                    const id = Number(cartBtn.dataset.id);
                    const current = cart.get(id) || 0;

                    if (cartBtn.dataset.cart === 'inc') {
                        cart.set(id, Math.min(MAX_QTY, current + 1));
                    } else if (cartBtn.dataset.cart === 'dec') {
                        current <= 1 ? cart.delete(id) : cart.set(id, current - 1);
                    } else {
                        cart.delete(id);
                    }
                    render();
                }
            });

            $('checkout-btn').addEventListener('click', function () {
                form.classList.remove('d-none');
                $('customer_name').focus({ preventScroll: true });
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });

            function showErrors(data, status) {
                let messages = [];

                if (data && data.errors) {
                    Object.keys(data.errors).forEach(function (key) {
                        [].concat(data.errors[key]).forEach(function (m) { messages.push(m); });
                    });
                }
                if (!messages.length) {
                    messages.push(status === 429
                        ? 'Too many attempts. Please wait a minute and try again.'
                        : ((data && data.message) || 'Something went wrong. Please try again.'));
                }

                formAlert.innerHTML = '<ul class="mb-0 ps-3">' +
                    messages.map(function (m) { return '<li>' + esc(m) + '</li>'; }).join('') + '</ul>';
                formAlert.classList.remove('d-none');
                formAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function setLoading(state) {
                submitting = state;
                submitBtn.disabled = state;
                submitBtn.querySelector('span').textContent = state ? 'Creating your order…' : 'Order on WhatsApp';
            }

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                if (submitting || cart.size === 0) return;

                formAlert.classList.add('d-none');
                setLoading(true);

                const payload = {
                    customer_name: form.customer_name.value.trim(),
                    customer_phone: form.customer_phone.value.trim(),
                    customer_address: form.customer_address.value.trim(),
                    items: Array.from(cart.entries()).map(function (entry) {
                        return { product_id: Number(entry[0]), quantity: entry[1] }; // no prices sent
                    }),
                };

                try {
                    const res = await fetch(ORDER_URL, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json().catch(function () { return {}; });

                    if (res.ok && data.whatsapp_url) {
                        cart.clear();
                        form.reset();
                        form.classList.add('d-none');
                        render();

                        successAlert.innerHTML = 'Order created! Opening WhatsApp… If nothing happens, ' +
                            '<a class="alert-link" href="' + esc(data.whatsapp_url) + '">tap here to send your order</a>.';
                        successAlert.classList.remove('d-none');

                        window.location.href = data.whatsapp_url;
                        return;
                    }

                    showErrors(data, res.status);
                } catch (err) {
                    showErrors({ message: 'Network error. Please check your connection and try again.' }, 0);
                } finally {
                    setLoading(false);
                }
            });

            render();
        })();
    </script>
</body>
</html>