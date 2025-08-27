<?php

namespace App\Jobs;

use App\Models\Order;
use App\Mail\OrderConfirmation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $this->order->load(['orderItems.ticketType', 'event', 'customer']);
        
        Mail::to($this->order->customer->email)
            ->send(new OrderConfirmation($this->order));
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Order confirmation email failed', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage()
        ]);
    }
}