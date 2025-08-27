<?php

namespace App\Jobs;

use App\Models\Order;
use App\Mail\TicketDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTicketDeliveryEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $this->order->load(['tickets.ticketType', 'event', 'customer']);
        
        Mail::to($this->order->customer->email)
            ->send(new TicketDelivery($this->order));
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Ticket delivery email failed', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage()
        ]);
    }
}