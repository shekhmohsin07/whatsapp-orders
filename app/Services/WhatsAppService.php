<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Store;

class WhatsAppService
{
    /**
     * Build the plain-text message the customer sends to the store.
     */
    public function generateOrderMessage(Order $order): string
    {
        $order->loadMissing(['items', 'store']);

        $store = $order->store;
        $currency = $store->currency;

        $lines = [
            "Hello {$store->name},",
            '',
            'I would like to order:',
            '',
        ];

        foreach ($order->items as $item) {
            $lines[] = "{$item->product_name} x{$item->quantity} - ".$this->formatMoney($item->subtotal, $currency);
        }

        $lines[] = '';
        $lines[] = 'Total: '.$this->formatMoney($order->total_amount, $currency);
        $lines[] = '';
        $lines[] = "Customer Name: {$order->customer_name}";
        $lines[] = "Phone: {$order->customer_phone}";
        $lines[] = "Address: {$order->customer_address}";
        $lines[] = '';
        $lines[] = 'Thank you.';

        return implode("\n", $lines);
    }

    /**
     * https://wa.me/{digits only}?text={url-encoded message}
     */
    public function generateWhatsAppUrl(Store $store, Order $order): string
    {
        // wa.me wants the number in international format with digits only.
        $number = preg_replace('/\D+/', '', $store->whatsapp_number);

        $message = $order->whatsapp_message ?: $this->generateOrderMessage($order);

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }

    /**
     * "$10", "$10.50", "USD 10". Whole amounts drop the ".00".
     */
    public function formatMoney(float|int|string $amount, string $currency): string
    {
        $formatted = number_format((float) $amount, 2, '.', ',');

        if (str_ends_with($formatted, '.00')) {
            $formatted = substr($formatted, 0, -3);
        }

        // Letter codes (USD, EUR) get a space; symbols ($, €, £) do not.
        return preg_match('/^[A-Za-z]+$/', $currency)
            ? "{$currency} {$formatted}"
            : "{$currency}{$formatted}";
    }
}