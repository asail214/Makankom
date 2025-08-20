@component('mail::message')
# Your Tickets

Order Number: {{ $order->order_number }}

Event: {{ $order->event->title }}

@component('mail::table')
| Ticket Number | Type | Status |
| :-- | :-- | :-- |
@foreach($order->tickets as $ticket)
| {{ $ticket->ticket_number }} | {{ $ticket->ticketType->name }} | {{ ucfirst($ticket->status) }} |
@endforeach
@endcomponent

You can also view your tickets in your account.

Thanks,
{{ config('app.name') }}
@endcomponent


