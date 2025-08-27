<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\Customer;
use App\Models\Event;
use App\Models\TicketType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::first();
        $event = Event::first();
        $ticketType = TicketType::first();

        if (!$customer || !$event || !$ticketType) {
            return;
        }

        $order = Order::create([
            'order_number' => 'ORD' . strtoupper(Str::random(8)),
            'customer_id' => $customer->id,
            'event_id' => $event->id,
            'subtotal' => 150.00,
            'total_amount' => 150.00,
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'ticket_type_id' => $ticketType->id,
            'quantity' => 2,
            'unit_price' => 75.00,
            'total_price' => 150.00,
        ]);

        // Create tickets
        for ($i = 0; $i < 2; $i++) {
            Ticket::create([
                'ticket_number' => 'TKT' . strtoupper(Str::random(8)),
                'order_item_id' => $orderItem->id,
                'customer_id' => $customer->id,
                'event_id' => $event->id,
                'ticket_type_id' => $ticketType->id,
                'status' => 'active',
                'qr_code' => strtoupper(Str::uuid()),
            ]);
        }
    }
}