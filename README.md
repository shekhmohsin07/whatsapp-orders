# WhatsApp Order Generator (MVP)

A small SaaS for restaurants, grocery stores, clothing shops and other local businesses. Owners build an online catalog. Customers browse the public store, fill a cart, and tap **Order on WhatsApp**. The order is saved and WhatsApp opens with a ready-made message to the store owner's number. There is no online payment.

**Stack:** Laravel 12, PHP 8.2+, MySQL, Laravel Sanctum, Blade + Bootstrap 5 (CDN).

## Quick start

```bash
composer create-project laravel/laravel whatsapp-orders
cd whatsapp-orders
php artisan install:api        # installs Sanctum and creates routes/api.php
# copy the project files from the chat into place, then:
```

Set these in `.env`:

```
APP_NAME="WhatsApp Orders"
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=whatsapp_orders
DB_USERNAME=root
DB_PASSWORD=
FILESYSTEM_DISK=public
```

Create the empty `whatsapp_orders` database, then:

```bash
composer install
php artisan migrate --seed
php artisan storage:link       # makes uploaded logos and images public
php artisan serve
```

If you change `.env` later, run `php artisan config:clear`.

**Demo login:** `demo@example.com` / `password`
**Demo store:** http://127.0.0.1:8000/store/abc-restaurant

The demo store uses the placeholder number `+15551234567`. Edit the store in the dashboard and set a real WhatsApp number to test the redirect.

## Features

- Business users register, log in and manage their own stores, products and orders.
- Customers need no account.
- Stores: create, edit, delete, activate/deactivate, logo upload, WhatsApp number, currency, automatic unique slug.
- Products: add, edit, delete, activate/deactivate, image upload, price, description.
- Orders: list with status filter, details page, status updates (pending, confirmed, completed, cancelled).
- Public store page (`/store/{slug}`), mobile-first, with a JavaScript cart and checkout form.
- REST API with Sanctum tokens, plus a public API for the storefront.

## Security highlights

- Prices are never taken from the client. `OrderService` loads them from the database.
- Every order line must be an active product of the store being ordered from.
- Orders are created in a database transaction.
- Policies stop users from reading or changing other users' stores, products and orders.
- `user_id`, `store_id` and order `status` are not mass-assignable.
- CSRF protection on web routes, throttling on login, register and public order creation.

## Project structure

```
app/
  Http/Controllers/Api/   AuthController, StoreController, ProductController,
                          OrderController, PublicStoreController
  Http/Controllers/Web/   AuthController, DashboardController, StoreController,
                          ProductController, OrderController, ProfileController,
                          PublicStoreController
  Http/Requests/          RegisterRequest, LoginRequest, StoreRequest, ProductRequest,
                          CreateOrderRequest, UpdateOrderStatusRequest
  Http/Resources/         UserResource, StoreResource, ProductResource,
                          OrderResource, OrderItemResource
  Models/                 User, Store, Product, Order, OrderItem
  Policies/               StorePolicy, ProductPolicy, OrderPolicy
  Services/               OrderService, WhatsAppService
  Traits/ApiResponse.php
database/migrations/      stores, products, orders, order_items
database/seeders/         DatabaseSeeder, DemoSeeder
resources/views/          layouts, auth, dashboard, public/store.blade.php
routes/                   api.php (REST), web.php (Blade)
tests/Feature/OrderCreationTest.php
```

## Database

```
users 1───N stores 1───N products
              stores 1───N orders 1───N order_items N───1 products (nullable)
```

`order_items` stores a snapshot of `product_name` and `price`, so deleting a product does not change past orders.

## API reference

All responses use one format.

```json
{ "success": true,  "message": "...", "data": {} }
{ "success": false, "message": "...", "errors": {} }
```

Login and register return `token` and `user` at the top level. Order creation returns `order` and `whatsapp_url`. Send `Accept: application/json` on every request.

### Authentication

| Method | Endpoint | Auth |
|---|---|---|
| POST | `/api/register` | No |
| POST | `/api/login` | No |
| POST | `/api/logout` | Bearer token |
| GET | `/api/user` | Bearer token |

### Business (Bearer token)

| Method | Endpoint |
|---|---|
| GET, POST | `/api/stores` |
| GET, PUT, DELETE | `/api/stores/{store}` |
| GET, POST | `/api/stores/{store}/products` |
| GET, PUT, DELETE | `/api/products/{product}` |
| GET | `/api/stores/{store}/orders` (optional `?status=pending`) |
| GET | `/api/orders/{order}` |
| PUT | `/api/orders/{order}/status` |

To upload a logo or image on update, send a `POST` with the form field `_method=PUT` (multipart forms do not work on a real `PUT`).

### Public (no auth)

| Method | Endpoint |
|---|---|
| GET | `/api/public/stores/{slug}` |
| GET | `/api/public/stores/{slug}/products` |
| POST | `/api/public/stores/{slug}/orders` |

Order request:

```json
{
  "customer_name": "John",
  "customer_phone": "+123456789",
  "customer_address": "123 Main Street",
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 3, "quantity": 1 }
  ]
}
```

Any `price` field sent is ignored.

## Try it with curl

```bash
BASE=http://127.0.0.1:8000/api

# Register (or log in) and copy the token
curl -X POST $BASE/register -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"name":"Owner","email":"owner@example.com","password":"password123"}'
TOKEN="paste-token-here"

# Create a store and a product
curl -X POST $BASE/stores -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"My Shop","whatsapp_number":"+15551234567","currency":"$"}'

curl -X POST $BASE/stores/1/products -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" -d '{"name":"Coke","price":2}'

# Public store, then place an order
curl $BASE/public/stores/my-shop -H "Accept: application/json"
curl -X POST $BASE/public/stores/my-shop/orders -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"customer_name":"John","customer_phone":"+123456789","customer_address":"123 Main Street","items":[{"product_id":1,"quantity":2}]}'

# Owner side: list orders and update status
curl $BASE/stores/1/orders -H "Accept: application/json" -H "Authorization: Bearer $TOKEN"
curl -X PUT $BASE/orders/1/status -H "Accept: application/json" -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" -d '{"status":"confirmed"}'
```

**Postman:** create an environment with `base_url` and `token`, add `Accept: application/json`, and put this in the Tests tab of Register and Login so the token saves itself:

```js
pm.environment.set("token", pm.response.json().token);
```

## Tests

```bash
php artisan test
```

The tests use in-memory SQLite (the `pdo_sqlite` PHP extension is needed), so your MySQL data is not touched. They cover price handling, inactive products, products from another store, validation format, inactive stores and authorization.

## Troubleshooting

- **Images look broken:** run `php artisan storage:link` and make sure `APP_URL` matches the address in your browser.
- **API errors show as HTML:** send `Accept: application/json`.
- **403 instead of 422 or the reverse:** validation runs before the ownership check, so send a valid body when testing 403 on `PUT` routes.
- **WhatsApp opens a wrong chat:** check the store's WhatsApp number. It needs the country code, and the app strips everything except digits for the `wa.me` link.

## Not included (by design)

Payments, subscription billing, email marketing, delivery management, inventory, multi-language support, notifications, a mobile app and a super-admin role. These can be added later.
