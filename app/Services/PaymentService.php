<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\Payment\ThawaniGateway;
use Illuminate\Support\Facades\Config;

class PaymentService
{
    protected bool $enabled;
    protected string $defaultGatewayName;
    protected PaymentGatewayInterface $gateway;

    public function __construct(?PaymentGatewayInterface $gateway = null)
    {
        $this->enabled = (bool) Config::get('payments.enabled', true);
        $this->defaultGatewayName = (string) Config::get('payments.default_gateway', 'thawani');
        $this->gateway = $gateway ?? $this->makeGateway($this->defaultGatewayName);
    }

    protected function makeGateway(string $name): PaymentGatewayInterface
    {
        switch ($name) {
            case 'thawani':
            default:
                return new ThawaniGateway();
        }
    }

    public function createCheckoutSession(Order $order, array $options = []): array
    {
        if (!$this->enabled) {
            throw new \RuntimeException('Payments are disabled');
        }
        $order->loadMissing('orderItems.ticketType');
        return $this->gateway->createCheckoutSession($order, $options);
    }

    public function handleWebhook(string $gateway, array $payload, ?string $signature = null): void
    {
        if (!$this->enabled) {
            return;
        }
        $this->makeGateway($gateway)->handleWebhook($payload, $signature);
    }

    public function refund(Payment $payment, ?float $amount = null): array
    {
        if (!$this->enabled) {
            throw new \RuntimeException('Payments are disabled');
        }
        return $this->gateway->refund($payment, $amount);
    }
}


