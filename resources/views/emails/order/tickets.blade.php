<x-mail::message>
# Your Tickets - Order {{ $order->order_number }}

Hello {{ $order->customer->first_name }},

Your tickets for "{{ $order->event->title }}" are ready!

**Event Details:**
- **Date:** {{ $order->event->start_date->format('F j, Y \a\t g:i A') }}
- **Venue:** {{ $order->event->venue_name }}
- **Address:** {{ $order->event->venue_address }}

**Your Tickets:**
@foreach($order->tickets as $ticket)
- **Ticket:** {{ $ticket->ticket_number }}
- **Type:** {{ $ticket->ticketType->name }}
- **Status:** {{ ucfirst($ticket->status) }}
@endforeach

Please save this email and present your QR codes at the venue entrance.

<x-mail::button :url="config('app.url') . '/tickets'">
View Digital Tickets
</x-mail::button>

**Important:** Take screenshots of your tickets as backup in case of connectivity issues at the venue.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>