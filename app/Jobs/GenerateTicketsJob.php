<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GenerateTicketsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function handle(): void
    {
        $this->order->loadMissing('orderItems.ticketType');
        
        foreach ($this->order->orderItems as $item) {
            for ($i = 0; $i < $item->quantity; $i++) {
                $ticket = Ticket::create([
                    'ticket_number' => $this->generateUniqueTicketNumber(),
                    'order_item_id' => $item->id,
                    'customer_id' => $this->order->customer_id,
                    'event_id' => $this->order->event_id,
                    'ticket_type_id' => $item->ticket_type_id,
                    'status' => 'active',
                    'qr_code' => $this->generateUniqueQrCode(),
                ]);
            }
        }

        // Dispatch ticket delivery email
        SendTicketDeliveryEmail::dispatch($this->order);
    }

    private function generateUniqueTicketNumber(): string
    {
        do {
            $number = strtoupper(uniqid('TKT'));
        } while (Ticket::where('ticket_number', $number)->exists());
        
        return $number;
    }

    private function generateUniqueQrCode(): string
    {
        do {
            $code = strtoupper(Str::uuid()->toString());
        } while (Ticket::where('qr_code', $code)->exists());
        
        return $code;
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('Ticket generation failed', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage()
        ]);
    }
}