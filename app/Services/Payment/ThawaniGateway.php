<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Contracts\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class ThawaniGateway implements PaymentGatewayInterface
{
    protected string $apiKey;
    protected string $baseUrl;
    protected ?string $webhookSecret;

    public function __construct()
    {
        $this->apiKey = config('payments.gateways.thawani.api_key');
        $this->baseUrl = rtrim(config('payments.gateways.thawani.base_url'), '/');
        $this->webhookSecret = config('payments.gateways.thawani.webhook_secret');
    }

    public function createCheckoutSession(Order $order, array $options = []): array
    {
        // Minimal placeholder aligning to Thawani docs concepts
        $response = Http::withToken($this->apiKey)
            ->post($this->baseUrl.'/checkout/session', [
                'client_reference_id' => (string) $order->order_number,
                'mode' => 'payment',
                'success_url' => config('payments.gateways.thawani.success_url'),
                'cancel_url' => config('payments.gateways.thawani.cancel_url'),
                'line_items' => $order->orderItems->map(function ($item) {
                    return [
                        'name' => $item->ticketType->name,
                        'quantity' => $item->quantity,
                        'unit_amount' => (int) round($item->unit_price * 100),
                    ];
                })->values()->all(),
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to create Thawani checkout: '.$response->body());
        }

        return $response->json();
    }

    public function handleWebhook(array $payload, string $signature = null): void
    {
        // Verify signature if required by Thawani (placeholder)
        // Process event types: payment.succeeded, payment.failed, refund.succeeded, etc.
        // Map to local Payment records and Orders
    }

    public function refund(Payment $payment, float $amount = null): array
    {
        $response = Http::withToken($this->apiKey)
            ->post($this->baseUrl.'/refunds', [
                'payment_id' => $payment->transaction_id,
                'amount' => (int) round(($amount ?? (float) $payment->amount) * 100),
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Failed to create refund: '.$response->body());
        }

        return $response->json();
    }
}


