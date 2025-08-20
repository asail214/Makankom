<?php

namespace App\Services\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    public function createCheckoutSession(Order $order, array $options = []): array;

    public function handleWebhook(array $payload, string $signature = null): void;

    public function refund(Payment $payment, float $amount = null): array;
}


