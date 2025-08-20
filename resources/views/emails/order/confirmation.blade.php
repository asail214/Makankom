@component('mail::message')
# Order Confirmation

Order Number: {{ $order->order_number }}

Event: {{ $order->event->title }}

@component('mail::table')
| Ticket Type | Qty | Unit Price | Total |
| :-- | --: | --: | --: |
@foreach($order->orderItems as $item)
| {{ $item->ticketType->name }} | {{ $item->quantity }} | {{ number_format($item->unit_price, 2) }} | {{ number_format($item->total_price, 2) }} |
@endforeach
@endcomponent

Subtotal: {{ number_format($order->subtotal, 2) }}

Total: {{ number_format($order->total_amount, 2) }}

Thanks,
{{ config('app.name') }}
@endcomponent


