<x-mail::message>
# Order Confirmation - {{ $order->order_number }}

Hello {{ $order->customer->first_name }},

Thank you for your order! Here are the details:

**Event:** {{ $order->event->title }}<br>
**Date:** {{ $order->event->start_date->format('F j, Y \a\t g:i A') }}<br>
**Venue:** {{ $order->event->venue_name }}

**Order Summary:**
@foreach($order->orderItems as $item)
- {{ $item->ticketType->name }} x{{ $item->quantity }} - ${{ number_format($item->total_price, 2) }}
@endforeach

**Total:** ${{ number_format($order->total_amount, 2) }}

Your tickets will be delivered separately via email.

<x-mail::button :url="config('app.url') . '/orders/' . $order->id">
View Order Details
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>