<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TicketDelivery extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function build(): self
    {
        return $this->subject('Your Tickets for Order '.$this->order->order_number)
            ->markdown('emails.order.tickets', [
                'order' => $this->order->loadMissing('tickets.ticketType', 'event'),
            ]);
    }
}


