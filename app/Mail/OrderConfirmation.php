<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
    }

    public function build(): self
    {
        return $this->subject('Your Order '.$this->order->order_number.' Confirmation')
            ->markdown('emails.order.confirmation', [
                'order' => $this->order->loadMissing('orderItems.ticketType', 'event'),
            ]);
    }
}


