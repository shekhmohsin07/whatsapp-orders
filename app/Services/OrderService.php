<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly WhatsAppService $whatsApp)
    {
    }

    /**
     * @param  array  $data  Validated input: customer_* fields and items[] (product_id, quantity).
     *
     * @throws ValidationException when a product is inactive or belongs to another store.
     */
    public function createOrder(Store $store, array $data): Order
    {
        return DB::transaction(function () use ($store, $data) {
            $lines = $this->buildLines($store, $data['items']);
            $totalCents = array_sum(array_column($lines, 'subtotal_cents'));

            // store_id comes from the relationship; status defaults to "pending".
            $order = $store->orders()->create([
                'customer_name' => $data['customer_name'],
                'customer_phone' => $data['customer_phone'],
                'customer_address' => $data['customer_address'],
                'total_amount' => $this->fromCents($totalCents),
            ]);

            foreach ($lines as $line) {
                $order->items()->create([
                    'product_id' => $line['product_id'],
                    'product_name' => $line['product_name'],
                    'quantity' => $line['quantity'],
                    'price' => $this->fromCents($line['price_cents']),
                    'subtotal' => $this->fromCents($line['subtotal_cents']),
                ]);
            }

            $order->load('items');
            $order->setRelation('store', $store);

            $order->whatsapp_message = $this->whatsApp->generateOrderMessage($order);
            $order->save();

            return $order;
        });
    }

    /**
     * Load the real products and price every line from the database.
     */
    private function buildLines(Store $store, array $items): array
    {
        $productIds = array_map(fn ($item) => (int) $item['product_id'], $items);

        // One query enforces both rules: the product belongs to THIS store and is active.
        $products = $store->products()
            ->active()
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $errors = [];
        $lines = [];

        foreach ($items as $index => $item) {
            $product = $products->get((int) $item['product_id']);

            if (! $product) {
                $errors["items.{$index}.product_id"] = ['This product is not available in this store.'];

                continue;
            }

            $priceCents = $this->toCents($product->price);
            $quantity = (int) $item['quantity'];

            $lines[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'price_cents' => $priceCents,
                'subtotal_cents' => $priceCents * $quantity,
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $lines;
    }

    private function toCents(float|int|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }
}